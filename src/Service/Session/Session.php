<?php
namespace PHPCrystal\PHPCrystal\Service\Session;

use PHPCrystal\PHPCrystal\Component\Service\AbstractContractor;
use PHPCrystal\PHPCrystal\Core\Service\Storage\Persistent\Filesystem\Filesystem;
use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Contract as Contract;

class Session extends AbstractContractor implements
	Contract\Session
{
	private $config;
	private $backendStorage;
	private $sessionId;
	private $container;
	private $flashContainer;
	private $startedFlag = false;
	
	private $useTransSid;
	private $sessionName;
	
	/**
	 * Generates unique session identifier
	 * 
	 * @return string
	 */
	private function generateSessionId()
	{
		$request = $this->getApplication()->getRequest();
		
		return md5($request->getRemoteIpAddr() . $request->getUserAgent() . microtime());
	}
	
	/**
	 * @return Container
	 */
	private function createContainer(array $items)
	{
		return Container::create('SessionData', $items);
	}
	
	/**
	 * @return Container
	 */
	private function createFlashContainer(array $items)
	{
		return FlashContainer::create('SessionFlashData', $items);
	}
	
	/**
	 * @return array
	 */
	private function fetchBulkData($sessId)
	{
		return $this->isStale($sessId) ?
			null : $this->backendStorage->get($sessId);
	}
	
	/**
	 * @return void
	 */
	private function saveBulkData($sessId)
	{
		$dataBulk = [$this->container->toArray(),
			$this->flashContainer->toArray()];
	
		$this->backendStorage->set($sessId, $dataBulk);
	}

	/**
	 * @return void
	 */
	private function unpackData($sessId)
	{
		$dataBulk = $this->fetchBulkData($sessId);

		if (empty($dataBulk)) {
			$data = $flashData = [];
		} else {
			$data = (array)array_shift($dataBulk);
			$flashData = (array)array_shift($dataBulk);
		}

		$this->container = $this->createContainer($data);
		$this->flashContainer = $this->createFlashContainer($flashData);
	}

	/**
	 * @return void
	 */
	public function init()
	{
		if ($this->isInitialized()) {
			return;
		}

		$app = $this->getApplication();
		$context = $app->getContext();
		$request = $app->getRequest();

		$this->config = $context->pluck('phpcrystal.session');
		
		$this->backendStorage = $this->config->get('storage');
		$this->backendStorage->init();
		
		$this->useTransSid = $this->config->get('use_trans_sid');
		$this->sessionName = $this->config->get('name');
		
		if ($this->useTransSid && $request->getGetInput()->has($this->sessionName)) {
			$this->sessionId = $request->getGetInput()
				->get($this->sessionName);
		} else if ($request->getCookieInput()->has($this->sessionName)) {
			$this->sessionId = $request->getCookieInput()
				->get($this->sessionName);
		}

		if (null !== $this->sessionId) {
			$this->unpackData($this->sessionId);
		} else if ($this->config->get('auto_start')) {
			$this->start();
		}
		
		$this->isInitialized = true;
	}
	
	/**
	 * @return
	 */
	public function finish()
	{
		parent::finish();
		
		if (null === $this->sessionId) {
			return;
		}
		
		// assign session id
		if ($this->startedFlag) {
			if ( ! $this->useTransSid) {
				setcookie(
					$this->sessionName,
					$this->sessionId,
					$this->config->get('cookie_lifetime'),
					$this->config->get('cookie_path'),
					$this->config->get('cookie_domain'),
					false,
					$this->config->get('cookie_httponly')
				);
			}
		}

		// save session data
		if ($this->container->hasChanges() || $this->flashContainer->hasChanges()) {
			$this->saveBulkData($this->sessionId);
		}
	}
	
	/**
	 * Returns session id
	 * 
	 * @return string
	 */
	public function getId()
	{
		return $this->sessionId;
	}
	
	/**
	 * @return bool
	 */
	public function isActive()
	{
		return $this->sessionId !== null;
	}
	
	/**
	 * @return bool
	 */
	public function isStale($sessId)
	{
		if ( ! $this->backendStorage->has($sessId)) {
			return true;
		}
		
		if ($this->backendStorage instanceof Filesystem) {
			$atime = $this->backendStorage->getAtime();
			$gcLifetime = $this->config->get('gc_maxlifetime');
			if (time() - $atime > $gcLifetime) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Starts a new session
	 * 
	 * @return void
	 */
	public function start($oldSessId = null)
	{
		$this->sessionId = $this->generateSessionId();

		if (null !== $oldSessId) {
			$this->saveBulkData($this->sessionId);
		} else {
			$this->container = $this->createContainer([]);
			$this->flashContainer = $this->createFlashContainer([]);
		}
		
		$this->startedFlag = true;
	}
	
	/**
	 * Regenerates session id keeping the current session data
	 * 
	 * @return void
	 */
	public function regenerateId()
	{
		$this->idRegenerationFlag = true;
		
		return $this->start($this->sessionId);
	}
	
	/**
	 * Flushes the current session data
	 * 
	 * @return void
	 */
	public function flush()
	{
		$this->container = new Container();
	}
	
	/**
	 * Returns a session data item
	 * 
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		return $this->container->get($key, $default);
	}
	
	/**
	 * @return void
	 */
	public function set($key, $value)
	{
		$this->container->set($key, $value);
	}
	
	/**
	 * Returns true if an item exists in the current session
	 * 
	 * @return bool
	 */
	public function has($key)
	{
		return $this->container->has($key);
	}
	
	/**
	 * @return void
	 */
	public function flash($key, $value)
	{
		
	}
}
