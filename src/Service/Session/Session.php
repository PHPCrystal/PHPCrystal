<?php
namespace PHPCrystal\PHPCrystal\Service\Session;

use PHPCrystal\PHPCrystal\Component\Service\AbstractContractor;
use PHPCrystal\PHPCrystal\Core\Service\Storage\Persistent\Filesystem\Filesystem;
use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Contract as Contract;
use PHPCrystal\PHPCrystal\Component\Http\Response\Header as HttpHeader;

class Session extends AbstractContractor implements
	Contract\Session
{
	private $config;
	private $backendStorage;
	private $sessionId;
	private $dataContainer;
	private $flashDataContainer;
	private $metaContainer;
	private $newSessionFlag = false;
	
	private $useTransSid;
	private $sessionName;
	
	/**
	 * @var integer
	 */
	private $expirationTime;
	
	/**
	 * @var integer
	 */
	private $cookieLifetime;
	
	/**
	 * @var integer
	 */
	private $gcLifetime;
	
	/**
	 * Generates unique session identifier
	 * 
	 * @return string
	 */
	private function generateSessionId()
	{
		$request = $this->getApplication()->getRequest();
		
		return md5($request->getRemoteAddr() . $request->getUserAgent() . microtime());
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
	 * @return Contaier
	 */
	private function createMetaContainer(array $items)
	{
		return MetaContainer::create('SessionMetaData', $items);
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
		$dataBulk = [
			$this->dataContainer->toArray(),
			$this->flashDataContainer->toArray(),
			$this->metaContainer->toArray()
		];
	
		$this->backendStorage->set($sessId, $dataBulk);
	}

	/**
	 * @return void
	 */
	private function unpackData($sessId)
	{
		$dataBulk = $this->fetchBulkData($sessId);

		if (empty($dataBulk)) {
			$data = $flashData = $metaData = [];
		} else {
			$data = (array)array_shift($dataBulk);
			$flashData = (array)array_shift($dataBulk);
			$metaData = (array)array_shift($dataBulk);
		}

		$this->dataContainer = $this->createContainer($data);
		$this->flashDataContainer = $this->createFlashContainer($flashData);
		$this->metaContainer = $this->createMetaContainer($metaData);
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
		$request = $app->getRequest();

		$this->config = $this->getServiceConfig();

		$this->backendStorage = $this->config->get('storage');
		$this->backendStorage->init();
		
		$this->useTransSid = $this->config->get('use_trans_sid');
		$this->sessionName = $this->config->get('name');
		$this->cookieLifetime = $this->config->get('cookie_lifetime');
		
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
	 * @return integer
	 */
	private function getCookieLifetime()
	{
		if ($this->metaContainer->has('cookie_lifetime')) {
			return $this->metaContainer->get('cookie_lifetime');
		} else  {
			$default = $this->config->get('cookie_lifetime');
			if ($default > 0) {
				return time() + $default;
			} else {
				return 0;
			}
		}		
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
		
		// set session cookie value
		if ($this->newSessionFlag && ! $this->useTransSid) {
			$cookie = HttpHeader\Cookie::create(
				$this->sessionName,
				$this->sessionId,
				$this->getCookieLifetime());
			
			$cookie
				->setPath($this->config->get('cookie_path'))
				->setDomain($this->config->get('cookie_domain'))
				->setHttpOnly($this->config->get('cookie_httponly'))
			;

			$cookie->save();
		}

		// save session data if necessary
		if ($this->dataContainer->hasChanges() ||
			$this->flashDataContainer->getCount() ||
			$this->metaContainer->hasChanges())
		{
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
	public function isAuthenticated()
	{
		return $this->isActive() && $this->metaContainer->assertTrue('is_auth');
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
	public function start($oldSessId = null, $expireTime =  null)
	{
		$this->sessionId = $this->generateSessionId();
		
		if (null === $oldSessId) {
			$this->dataContainer = $this->createContainer([]);
			$this->flashDataContainer = $this->createFlashContainer([]);
			$this->metaContainer = $this->createMetaContainer([]);
		}
		
		if (null === $expireTime) {
			$expireTime = time() + $this->config->get('gc_maxlifetime');
		} else {
			$this->metaContainer->set('cookie_lifetime', $expireTime);
		}
		
		$this->metaContainer->set('expire', $expireTime);
		$this->metaContainer->set('is_auth', false);
		$this->metaContainer->set('create_time', time());
		
		$this->newSessionFlag = true;
	}
	
	/**
	 * Regenerates session id keeping the current session data
	 * 
	 * @return void
	 */
	public function regenerateId()
	{
		$this->start($this->sessionId);
	}
	
	/**
	 * @return void
	 */
	public function remember($period)
	{
		$this->start($this->sessionId, time() + $period);
	}
	
	/**
	 * Flushes the current session data
	 * 
	 * @return void
	 */
	public function flush()
	{
		$this->dataContainer->flush();
		$this->flashDataContainer->flush();
	}
	
	/**
	 * Returns a session data item
	 * 
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		return $this->dataContainer->get($key, $default);
	}
	
	/**
	 * @return void
	 */
	public function set($key, $value)
	{
		$this->dataContainer->set($key, $value);
	}
	
	/**
	 * Returns true if an item exists in the current session
	 * 
	 * @return bool
	 */
	public function has($key)
	{
		return $this->dataContainer->has($key);
	}
	
	/**
	 * @return mixed
	 */
	public function getFlash($key)
	{
		return $this->flashDataContainer->get($key);
	}
	
	/**
	 * @return void
	 */
	public function setFlash($key, $value)
	{
		$this->flashDataContainer->set($key, $value);
	}
	
	/**
	 * @return bool
	 */
	public function hasFlash($key)
	{
		return $this->flashDataContainer->has($key);
	}
}
