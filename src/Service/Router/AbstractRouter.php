<?php
namespace PHPCrystal\PHPCrystal\Service\Router;

use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Component\Factory as Factory;
use PHPCrystal\PHPCrystal\Component\Http\Request;
use PHPCrystal\PHPCrystal\Component\Http\Uri;
use PHPCrystal\PHPCrystal\Component\Service\AbstractService;
use PHPCrystal\PHPCrystal\Facade\Metadriver;

abstract class AbstractRouter extends AbstractService
{
	protected $protocol;
	protected $hostname;
	protected $uriPathPrefix;
	private $isActive;

	protected $frontController;
	protected $controller;
	protected $action;
	
	private $actions = array();
	
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * {@inherited}
	 */
	final public static function isSingleton()
	{
		return false;
	}

	/**
	 * @return boolean
	 */
	final protected function matchRequest(Request $request)
	{
		$match = false;
		
		if ( ! empty($this->hostname) && $this->hostname == $request->getHostname()) {
			$match = true;
		}

		if ( ! empty($this->uriPathPrefix)) {
			$match = strpos($request->getUri()->getPath(), $this->uriPathPrefix) === 0 ?
				true : false;
		}
		
		if ($match) {
			$this->isActive = true;
		}
		
		return $match;
	}

	/**
	 * @return void
	 */
	public function init()
	{
		$context = $this->getApplication()->getContext();
		$this->protocol = 'http';
		$this->hostname = $context->get('app.hostname');
		$this->uriPathPrefix = '/';
	}
	

	/**
	 * @return void
	 */
	protected function triggerResponse404(Event\Type\Http\Request $event, $resEvent)
	{
		$event->setAutoTriggerEvent($resEvent);
		$event->discard();
	}	
	
	/**
	 * All routers are being initialized after application was boostraped
	 * 
	 * @return boolean
	 */
	final public static function hasLazyInit()
	{
		return true;
	}
	
	/**
	 * @return null
	 */
	final public function addAction($instance)
	{
		$this->actions[] = $instance;
	}
	
	/**
	 * @return array
	 */
	final protected function getAllActions()
	{
		return $this->actions;
	}

	/**
	 * @return
	 */
	final public function getFrontController()
	{
		return $this->frontController;
	}
	
	/**
	 * @return
	 */
	final public function getController()
	{
		return $this->controller;
	}
	
	/**
	 * @return
	 */
	final public function getAction()
	{
		return $this->action;
	}
	
	/**
	 * @return string
	 */
	final public function getHostname()
	{
		return $this->hostname;
	}
	
	/**
	 * @return string
	 */
	final public function getUriPathPrefix()
	{
		return $this->uriPathPrefix;
	}
	
	/**
	 * @return string
	 */
	final public function getProtocol()
	{
		return $this->protocol;
	}
	
	/**
	 * @return Uri
	 */
	public function getBaseUri()
	{
		$baseUriStr = $this->getProtocol() . '://' . $this->getHostname() .
			$this->getUriPathPrefix();
		
		return new Uri($baseUriStr);
	}

	public function isActive()
	{
		return $this->isActive;
	}

	/**
	 * @return bool
	 */
	protected function skipAction($action)
	{
		return $this->getApplication()->getCoreExtension() ===
			Metadriver::getOwnerInstance($action) ? false : true;
	}

	/**
	 * @return bool
	 */
	public function process(Event\Type\Http\Request $event)
	{
		if ( ! $this->matchRequest($event->getRequest())) {
			return false;
		}

		$pkgActions = $this->getApplication()
			->getPackageActions($this->getPackage());

		foreach ($pkgActions as $action) {

			if ($this->skipAction($action)) {
				continue;
			}

			if ($action->matchRequest($event->getRequest())) {
				$this->action = $action;

				$this->controller = $this->getFactory()
					->createControllerByAction($action);

				$this->frontController = $this->getFactory()
					->createFrontControllerByAction($action);

				return true;
			}
		}

		$this->triggerResponse404($event, Event\Type\Http\Response404::create());		

		return false;
	}
}
