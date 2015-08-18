<?php
namespace PHPCrystal\PHPCrystal\Component\Package;

use PHPCrystal\PHPCrystal\Component\Package\Option\Container;
use PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper;
use PHPCrystal\PHPCrystal\Facade\Metadriver;
use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Component\Facade as Facade;
use PHPCrystal\PHPCrystal\Component\Exception as Exception;
use PHPCrystal\PHPCrystal\Facade\SecurityGuard;

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
	 * @api
	 */
	public function __construct(\Composer\Autoload\ClassLoader $autoloader)
	{
		self::$autoloader = $autoloader;
		self::$extMinHeap = new Heap\MinHeap();
		Facade\AbstractFacade::setApplication($this);
		$this->setApplication($this);
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
			Exception\System\FrameworkRuntimeError::create('Output buffer is not empty "%s"', null, $outputStr)
				->addParam($outputStr)
				->_throw();
		} else {
			$this->clearOutputBuffer();
		}
	}

	/**
	 * @return void
	 */
	public function init()
	{
		parent::init();
	}

	/**
	 * @inherited
	 */
	final public function setDirectory($pathname)
	{
		FileHelper::addAlias('app', $pathname);

		return parent::setDirectory($pathname);
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
	

	final public function getCoreExtension()
	{
		return self::$extMinHeap->top();
	}

	/**
	 * @return void
	 */
	private function addExtensionInstance($extInstance)
	{
		// do not allow to add the same extension twice
		foreach ($this->getExtensions() as $loadedExt) {
			if ($loadedExt->getNamespace() == $extInstance->getNamespace()) {
				Exception\System\FrameworkRuntimeError::create('The extension "%s" is already loaded',
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
		$extInstance = FileHelper::create($extDirPath, 'bootstrap.php')
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

	public function getPackageActions($pkgInstance)
	{
		$result = [];
		
		foreach ($this->actions as $action) {
			if ($action->getPackage() === $pkgInstance) {
				$result[] = $action;
			}
		}
		
		return $result;
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

			FileHelper::create($pkg->getDirectory(), 'manifest.php')
				->requireIfExists($newContext);

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
		$context = $this->getContext();
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
				$this->addAction($action);
			}
		}		
	}

	/**
	 * @return 
	 */
	protected function addPathAliases()
	{	
		FileHelper::addAlias('cache', '@app/cache');
		FileHelper::addAlias('web', '@app/public_html');
		FileHelper::addAlias('template', '@app/resources/template');
		FileHelper::addAlias('tmp', '@app/tmp');		
	}
	
	/**
	 * Autoloads application extensions using composer.lock file
	 * 
	 * @return void
	 */
	private function autoloadExtensions()
	{
		if ($this->getContext()->getEnv() != 'prod') {
			Metadriver::flush();
			Metadriver::addExtensionsToAutoload();
		}

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
			parent::dispatch(Event\Type\System\Build::create());
		}
	}

	/**
	 * @return void
	 */
	private function addServices()
	{
		foreach (Metadriver::getContractors() as $metaservice) {
			$this->getFactory()->addMetaService($metaservice);
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
		// assign application directory path
		if ( ! $this->getDirectory()) {
			$this->setDirectory(realpath($this->getClassPath(get_class($this)) . '/..'));
		}
		// set event being dispatched
		$this->setCurrentEvent($externalEvent);		
		// create event context
		$this->context = $externalEvent->createContext();
		$this->addPathAliases();
		$this->autoloadExtensions();
		$this->flattenManifestFile($externalEvent);
		$this->buildApp();
		$this->addServices();
		$this->assignActions();
		$this->assignEventListeners();
		
		
		$this->addRouter('PHPCrystal\\PHPCrystal\\Service\\Router\\_Default');
		
		parent::dispatch(
			Event\Type\System\PkgNotification::create('load-config-file')
		);

		parent::dispatch(
			Event\Type\System\PkgNotification::create('init-routing')
		);		
		
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
			foreach ($pkg->getRouters() as $router) {
				$success = $router->process($event);
				
				if ($event->getStatus() == Event\STATUS_DISCARDED) {
					return $this;
				}

				if ($success) {
					$package = $router->getPackage();
					break;
				}
			}
		}

		// if discarded then event was designated to the current router but
		// routing by some reason failed
		if ( ! $success) {
			return $this;
		}

		$fcInstance = $router->getFrontController();
		$ctrlInstance = $router->getController();
		$actionInstance = $router->getAction();

		// form a propagation path through which the event will be passed.
		// Package -> Front Controller -> Controller -> Action
		$package
			->dispatchChainAddElement($fcInstance)
			->dispatchChainAddElement($ctrlInstance)
			->dispatchChainAddElement($actionInstance);
		
		if (null !== $fcInstance && null !== $ctrlInstance && null !== $actionInstance) {
			$fcInstance->mergePriorEvents($event, $ctrlInstance, $actionInstance);
		}

		// hook default services into corresponding events
		if ($fcInstance && $this->context->get('phpcrystal.security_guard.enabled')) {
			$fcInstance->addEventListener(Event\Type\System\SecurityPolicyApplication::toType(), function($event) {
				$securityGuard  = SecurityGuard::create(); 
				return $securityGuard->process($event);
			});
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
			$event->setException($e);
			$event->setStatus(Event\STATUS_INTERRUPTED);

			if ($e instanceof Exception\AbstractException &&
				Exception\AbstractException::$nonMaskable)
			{
				throw $e;
			}
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
