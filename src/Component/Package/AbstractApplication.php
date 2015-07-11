<?php
namespace PHPCrystal\PHPCrystal\Component\Package;

use PHPCrystal\PHPCrystal\Component\Filesystem\PathResolver;
use PHPCrystal\PHPCrystal\Facade\Metadriver;
use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Component\Facade as Facade;
use PHPCrystal\PHPCrystal\Component\Exception as Exception;

abstract class AbstractApplication extends AbstractPackage
{
	/**
	 * If set to true extensions will be autoloaded from the composer packages
	 * 
	 * @var bool
	 */
	private $extensionAutoload = true;

	private $bootstrapFlag = false;
	private $actions = array();
	private $context;
	private $requestEvent;
	
	/**
	 * @var \SplMinHeap
	 */
	protected static $extMinHeap;	
	
	/**
	 * 
	 */
	public function __construct()
	{
		Facade\AbstractFacade::setApplication($this);
		$this->setApplication($this);
		self::$extMinHeap = new Heap\MinHeap();
		$this->setPriority(999);		
		parent::__construct();		
	}
	
	/**
	 * @return bool
	 */
	final public function getExtensionAutoloadFlag()
	{
		return $this->extensionAutoload;
	}
	
	/**
	 * @return void
	 */
	final public function setExtenstionAutoloadFlag($flagValue)
	{
		$this->extensionAutoload = $flagValue;
	}	
	
	/**
	 * @return void
	 */
	private function clearOutputBuffer()
	{
		while (ob_get_level()) {
			ob_end_clean();
		}
	}
	
	/**
	 * @return void
	 */
	private function checkOutputBuffer()
	{
		if ($this->getContext()->getEnv() == 'dev' &&
			strlen(($outputStr = ob_get_clean()) > 0))
		{
			Exception\System\LastChineseWarning::create('Output buffer is not empty "%s"', null, $outputStr)
				->addParam($outputStr)
				->_throw();
		} else {
			$this->clearOutputBuffer();
		}
	}

	/**
	 * @return this
	 */
	final public static function create(\Composer\Autoload\ClassLoader $autoloader)
	{
		try {
			static::$autoloader = $autoloader;
			$appInstance = new static();
			$appInstance->init();
		} catch (\Exception $e) {
			exit;
		}
		
		return $appInstance;
	}

	/**
	 * @return void
	 */
	public function init()
	{

		parent::init();
	}
	
	/**
	 * @return boolean
	 */
	final public function isCli()
	{
		return php_sapi_name() === 'cli';
	}	

	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Package[]
	 */
	final public function getExtensions($withAppPkg = false)
	{
		$minHeap = clone self::$extMinHeap;
		
		if ($withAppPkg) {
			$minHeap->insert($this);
		}
		
		return iterator_to_array($minHeap);
	}

	/**
	 * @return void
	 */
	private function addExtensionInstance($extInstance)
	{
		// do not allow to add the same extension twice
		foreach ($this->getExtensions() as $loadedExt) {
			if ($loadedExt->getNamespace() == $extInstance->getNamespace()) {
				Exception\System\LastChineseWarning::create('The extension "%s" is already loaded',
					null, $extInstance->getComposerName())
					->_throw();
			}
		}
		
		if ($extInstance->getPriority() === null) {
			$extInstance->setPriority($this->getPriority() - 1);
		}

		self::$extMinHeap->insert($extInstance);		
		$this->addChild($extInstance);		
	}
	
	/**
	 * Adds a new extension to the application package
	 * 
	 * @return $this
	 */
	final public function addExtension($extDirPath)
	{
		$extInstance = PathResolver::create($extDirPath, 'bootstrap.php')
			->_require();

		if ( ! $extInstance->getDisabledFlag()) {		
			$this->addExtensionInstance($extInstance);
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	final public function getPackageByKey($key)
	{
		foreach ($this->getExtensions(true) as $package) {
			if ($package->getKey() == $key) {
				return $package;
			}
		}
	}

	/**
	 * @return \PHPCrystal\PHPCrystal\Component\MVC\Controller\AbstractAction[]
	 */
	final public function getAllActions()
	{
		return $this->actions;
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Component\MVC\Controller\AbstractAction[]
	 */
	final public function getValidActions()
	{
		$result = array();
		
		foreach ($this->getAllActions() as $action) {
			if ($action->isValid()) {
				$result[] = $action;
			}
		}

		return $result;
	}
	
	/**
	 * @return $this
	 */
	final public function addAction($actionInstance)
	{
		$this->actions[] = $actionInstance;
		
		return $this;
	}

	/**
	 * @return
	 */
	public function getContext()
	{
		return $this->context;
	}
	
	/**
	 * 
	 */
	final public function getRequestEvent()
	{
		return $this->requestEvent;
	}
	
	/**
	 * 
	 */
	final public function getRequest()
	{
		return $this->getCurrentEvent()->getRequest();
	}
	
	/**
	 * @return void
	 */
	private function finishContractServices()
	{
		foreach ($this->getFactory()->getContractServices() as $service) {
			if ($service->isInitialized()) {
				$service->finish();
			}
		}
	}
	
	/**
	 * @return void
	 */
	private function flattenManifestFile($externalEvent)
	{
		$target = null;		

		foreach ($this->getExtensions(true) as $pkg) {
			$newContext = $externalEvent->createContext();
			PathResolver::create($pkg->getDirectory(), 'manifest.php')
				->_require($newContext);
			if ($target) {
				$target->merge($newContext);
			} else {
				$target = $newContext;
			}
		}
		
		$this->context = $target;
	}

	/**
	 * @return void
	 */
	protected function assignEventListeners()
	{
		//$this->addEventListener(Event\Type\Cli\Command\Query::toType(), function($event) {
		//	return $this->onQueryCommand($event);
		//});		
	}
	
	/**
	 * @return void
	 */
	private function assignActions()
	{
		foreach (Metadriver::getActionsAll() as $pkgKey => $metaClassArray) {
			$package = $this->getPackageByKey($pkgKey);
			foreach ($metaClassArray as $metaClass) {
				$action = $package->getFactory()
					->createFromMetaClass($metaClass);

				$ruleAnnot = $metaClass->getActionAnnotation();
				$ctrlMethodAnnot = $metaClass->getControllerMethodAnnotation();
				$action->setAllowedHttpMethods($ruleAnnot->getAllowedHttpMethods());
				$action->setControllerMethod($ctrlMethodAnnot->value);
				$action->setUriMatchRegExp($ruleAnnot->getUriMatchRegExp());
				$action::setURIMatchPattern($ruleAnnot->matchPattern);

				$this->addAction($action);
			}
		}		
	}

	/**
	 * @return 
	 */
	protected function addPathAliases()
	{
		PathResolver::addAlias('app', $this->getDirectory(), false);
		PathResolver::addAlias('core', __DIR__ . '/../..');
	}
	
	/**
	 * @return void
	 */
	private function autoloadExtensions()
	{
		foreach (Metadriver::getExtensionsAll() as $metaExt) {
			$this->addExtension($metaExt->getDirectoryName());
		}
	}

	/**
	 * @return void
	 */
	private function buildApp()
	{
		if ($this->getContext()->getEnv() != 'prod') {
			Metadriver::flush();
			Metadriver::addExtensionsToAutoload();
			if ($this->getExtensionAutoloadFlag()) {
				$this->autoloadExtensions();
			}		
			parent::dispatch(Event\Type\System\Build::create());
		} else {
			if ($this->getExtensionAutoloadFlag()) {
				$this->autoloadExtensions();
			}
		}
	}

	/**
	 * @return void
	 */
	private function addServices()
	{
		foreach (Metadriver::getExportedServices() as $metaservice) {
			$this->getFactory()->addMetaService($metaservice);
		}		
	}
	
	/**
	 * @return void
	 */
	private function initRouting()
	{
		// router service must have lazy initialization. it's being initialized
		// after application is built
		foreach ($this->getExtensions(true) as $package) {
			$router = $package->getRouter();
			if ($router) {
				$router->init();
			}
		}	
	}

	/**
	 * @return $this
	 */
	public function bootstrap($externalEvent)
	{
		if ($this->bootstrapFlag) {
			return;
		}
		$this->addPathAliases();
		$this->assignEventListeners();		
		$this->flattenManifestFile($externalEvent);
		$this->buildApp();
		$this->addServices();
		$this->assignActions();
		$this->initRouting();
		$this->bootstrapFlag = true;
		
		return $this;
	}

	/**
	 * @return void
	 */
	protected function getTargetPackage($event)
	{
		$success = false;
		
		foreach ($this->getExtensions(true) as $pkg) {
			// not all packages require routing
			if ( ! $pkg->getRouter()) {
				continue;
			}
			
			$router = $pkg->getRouter();
			$success = $router->handle($event);
			
			// if discarded then event was designated to the current router but
			// routing by some reason failed
			if ($event->getStatus() == Event\STATUS_DISCARDED) {
				return $this;
			}
			
			if ($success) {
				$package = $router->getPackage();
				break;
			}
		}
		
		if ( ! $success) {
			return $this;
		}

		// form a propagation path through which the event will be passed.
		// Package -> Front Controller -> Controller -> Action
		
		$fcInstance = $router->getFrontController();
		$ctrlInstance = $router->getController();
		$actionInstance = $router->getAction();

		$package
			->dispatchChainAddElement($fcInstance)
			->dispatchChainAddElement($ctrlInstance)
			->dispatchChainAddElement($actionInstance);
		
		if (null !== $fcInstance && null !== $ctrlInstance && null !== $actionInstance) {
			$fcInstance->mergePriorEvents($ctrlInstance, $actionInstance);
		}
		
		// actions are terminate nodes
		$event->setTerminateNodeHandler(function($event) {
			return $this->execute($event);
		});
		
		return $package;
	}

	/**
	 * @return void
	 */
	final public function dispatch($event)
	{
		static $reentered = false;

		try {
			// handle internal events
			if ($event instanceof Event\Type\AbstractInternal || $reentered) {
				return parent::dispatch($event);
			}
			
			$event->setOriginalTarget($this);			
			$this->setCurrentEvent($event);
			
			// bootstrap
			$this->bootstrap($event);
			
			// dispatch given event to the application
			if ($event instanceof Event\Type\Http\Request) {
				$reentered = true;				

				$pkgTarget = $this->getTargetPackage($event);
				$event = $pkgTarget->dispatch($event);

				if ( ! $this->isCli()) {
					//$this->checkOutputBuffer();
				}
			} else {
				$event = parent::dispatch($event);
			}
		} catch (\Exception $e) {
			var_dump($e); exit;
			$event->setException($e);
			$event->setStatus(Event\STATUS_INTERRUPTED);
		}
		
		if ($event->getStatus() == Event\STATUS_INTERRUPTED) {
			$excepEvent = Event\Type\System\Exception::create()
				->setException($event->getException());
			$event = parent::dispatch($excepEvent);
		}
		
		$this->finishContractServices();

		if ($event instanceof Event\Type\InternalEventInterface) {
			$event->output();
		}
			
		return $event;
	}

	//
	// Event hooks
	//
}
