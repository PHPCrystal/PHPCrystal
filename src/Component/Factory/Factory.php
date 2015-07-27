<?php
namespace PHPCrystal\PHPCrystal\Component\Factory;

use PHPCrystal\PHPCrystal\Component\Service\MetaService;
use PHPCrystal\PHPCrystal\Service\Metadriver as Metadriver;
use PHPCrystal\PHPCrystal\Component\Service\AbstractService;
use PHPCrystal\PHPCrystal\Component\Service\AbstractContractor;
use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Facade\Metadriver as FacadeMetadriver;
use PHPCrystal\PHPCrystal\Component\Facade as Facade;

const DI_INTERFACE = 'PHPCrystal\\PHPCrystal\\Component\\Factory\\Aware\\DependencyInjectionInterface';

final class Factory
{
	/**
	 * @var PHPCrystal\PHPCrystal\Component\Package\AbstractPackage
	 */
	private $package;
	private static $singletonStorage = array();
	private static $metaservice = array();

	/**
	 * @var array
	 */
	private $dependenciesTracker;
	
	public function __construct($package)
	{
		$this->package = $package;
	}
	
	/**
	 * @return boolean
	 */
	public static function hasInterface($className, $interface)
	{
		$refClass = new \ReflectionClass($className);
		
		return $refClass->implementsInterface($interface);
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Package\AbstractPackage
	 */
	public static function getApplication()
	{
		return Facade\AbstractFacade::getApplication();
	}
	
	/**
	 * @return PHPCrystal\PHPCrystal\Component\Service\AbstractContract[]
	 */
	public function getContractServices()
	{
		$result = [];
		
		foreach (static::$singletonStorage as $instance) {
			if ($instance instanceof AbstractContractor) {
				$result[] = $instance;
			}
		}
		
		return $result;
	}
	
	/**
	 * @return void
	 */
	private function bind($newInstance)
	{
		if ($newInstance instanceof Aware\PackageInterface) {
			$newInstance->setPackage($this->package);
		}
		
		if ($newInstance instanceof Aware\FactoryInterface) {
			$newInstance->setFactory($this);
		}
		
		if ($newInstance instanceof Aware\ApplicationInterface) {
			$newInstance->setApplication(self::getApplication());
		}
	}

	/**
	 * @return boolean
	 */
	private function singletonHasInstance($className)
	{
		return in_array($className, array_keys(self::$singletonStorage));
	}
	
	/**
	 * @return object
	 */
	private function singletonGetInstance($className)
	{
		return self::$singletonStorage[$className];
	}
	
	/**
	 * @return object
	 */
	private function singletonNewInstance($className, ...$constArgs)
	{
		$newInstance = new $className(...$constArgs);
		self::$singletonStorage[$className] =  $newInstance;
		
		return $newInstance;
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Package\AbstractPackage
	 */
	final public function getPackage()
	{
		return $this->package;
	}
	
	/**
	 * @return object
	 */
	private function newInstance($className, $constArgs = array())
	{
		$instance = new $className(...$constArgs);		
		$this->bind($instance);		

//		if ($instance instanceof InitiableInterface) {
//			$instance->init();
//		}

		return $instance;
	}

	/**
	 * @return AbstractService
	 */
	public function newServiceInstance($className, $args = array())
	{
		if ($className::isSingleton() && $this->singletonHasInstance($className)) {
			return $this->singletonGetInstance($className);
		}
		
		// create service instance
		$service = $className::isSingleton() ?
			$this->singletonNewInstance($className, ...$args) :
			new $className(...$args);
		
		// bind required objects
		$this->bind($service);
		
		// dispatch init service event
		//$initServiceEvent = $this->getPackage()
		//	->getApplication()
		//	->dispatchInitServiceEvent($service);		
		//if ($initServiceEvent->hasCustomInitRoutine()) {
		//	$service->setCustomInitClosure($initServiceEvent->getResult());
		//}
		
		// initialize service if necessary.
		if ( ! ($service->isInitialized() || $className::hasLazyInit())) {
			$service->init();
		}

		return $service;
	}

	/**
	 * @return boolean
	 */
	private function circularReferenceCheck($input)
	{
		return count(array_unique($input)) != count($input) ? true : false;		
	}
	
	/**
	 * @return AbstractService
	 */
	public function create($className, $recursionDepth = 0)
	{
		static $originClassName = null;
		
		if (AbstractService::isService($className) && $className::isSingleton() &&
			$this->singletonHasInstance($className))
		{
			return $this->singletonGetInstance($className);
		}
		
		if ($recursionDepth == 0) {
			$originClassName = $className;
			$this->dependenciesTracker = array();
		}
		
		if ($this->circularReferenceCheck($this->dependenciesTracker)) {
			throw new \RuntimeException(sprintf('A circular dependency of the class "%s" has been detected',
				$originClassName));
		}
		
		$deps = array();
		foreach ($this->getClassDeps($className) as $metaservice) {
			if ($metaservice->isIdle()) {
				$deps[] = null;
				continue;
			}
			$depClassName = $metaservice->getClassName();
			$newService = $this->create($depClassName, $recursionDepth + 1);
			$deps[] = $newService;
			$this->dependenciesTracker[] = $depClassName;
		}
		
		if (self::hasInterface($className, DI_INTERFACE) &&
			$className::fireEventUponInstantiation())
		{
			$dIEvent = Event\Type\System\DependencyInjection::create($className, $deps);
			$this->getApplication()->dispatch($dIEvent);
			$deps = $dIEvent->getDependencies();
		}

		$newInstance = AbstractService::isService($className) ?
			$this->newServiceInstance($className, $deps) :
			$this->newInstance($className, $deps);
		
		return $newInstance;
	}
	
	/**
	 * @return 
	 */
	public function createByInterface($interface)
	{
		$meta = $this->getMetaServiceByInterface($interface);
		
		return $this->create($meta->getClassName());
	}
	
	/**
	 * @return object
	 */
	public function createFromMetaClass($metaClass)
	{
		$targetClass = $metaClass->getTargetClass();		
		$newInstance = $this->create($targetClass);

		foreach ($metaClass->getEventCatalystAnnotations() as $annot) {
			$newInstance->addPriorEvent($annot->getEvent());
		}

		$newInstance->setExtendableInstance($metaClass);

		if ($newInstance instanceof InitiableInterface) {
			$newInstance->init();
		}

		return $newInstance;
	}

	/**
	 * @return object
	 */
	public function createControllerByAction($action)
	{
		$metaClass = FacadeMetadriver::getControllerMetaClassByAction($action);
		
		return $this->createFromMetaClass($metaClass);
	}

	/**
	 * @return object
	 */
	public function createFrontControllerByAction($action)
	{
		$metaClass = FacadeMetadriver::getFrontControllerMetaClassByAction($action);
		
		return $this->createFromMetaClass($metaClass);	
	}

	/**
	 * @return boolean
	 */
	private function hasDeps($className)
	{
		$refClass = new \ReflectionClass($className);

		return
			$refClass->getConstructor() &&
			$refClass->getConstructor()->getParameters() > 0 &&
			(self::hasInterface($className, DI_INTERFACE) ||
				AbstractService::isService($className))
		;
	}
	
	/**
	 * @return $this
	 */
	public function addMetaService(MetaService $meta)
	{
		$interface = $meta->getInterface();

		if (!isset(self::$metaservice[$interface])) {
			self::$metaservice[$interface] = new \SplPriorityQueue();
			self::$metaservice[$interface]->setExtractFlags(\SplPriorityQueue::EXTR_DATA);
		}
		
		self::$metaservice[$interface]->insert($meta, $meta->getPriority());
		
		return $this;
	}

	/**
	 * @return Metaservice
	 */
	public function getMetaServiceByInterface($interface)
	{
		foreach (self::$metaservice as $interfaceKey => $splQueue) {
			if ($interface == $interfaceKey) {
				return $splQueue->top();
			}
		}

		throw new \RuntimeException(sprintf('Required service "%s" has not been found',
			$interface));
	}
	
	private function getMetaServiceByTypeHint(\ReflectionParameter $param)
	{
		$typeHinted = $param->getClass();
		
		if ($typeHinted->isInterface()) {
			$metaService = $this->getMetaServiceByInterface($typeHinted->name);
		} else if ($typeHinted->isInstantiable()) {
			$metaService = new MetaService($typeHinted->name, null, 999);
		}
		
		return $metaService;
	}
	
	/**
	 * return void
	 */
	private function checkWakeupEvents($metaClass, $isDependencyOptional)
	{
		$serviceClass = $metaClass->getClassName();		
		if ( ! $serviceClass::getWakeupEvents()) {
			return;
		}
		
		$currentEvent = $this->getApplication()->getCurrentEvent();
		$wakeupFlag = false;		
		foreach ($serviceClass::getWakeupEvents() as $wakeupEvent) {
			if ($currentEvent instanceof $wakeupEvent) {
				$wakeupFlag = true;
				break;
			}
		}
		
		if ( ! $wakeupFlag && ! $isDependencyOptional) {
			throw new \RuntimeException('Service injection parameter has to be optional');
		}
		
		if ( ! $wakeupFlag) {
			$metaClass->setIdle(true);			
		}
	}

	/**
	 * @return array
	 */
	private function getClassDeps($className)
	{
		if ( ! $this->hasDeps($className)) {
			return array();
		}
		
		$result = array();	
		$refClass = new \ReflectionClass($className);
		foreach ($refClass->getConstructor()->getParameters() as $param) {
			$typeHinted = $param->getClass();
			if ($typeHinted->isInterface()) {
				$metaClass = $this->getMetaServiceByInterface($typeHinted->name);
				
			} else if ($typeHinted->isInstantiable()) {
				$metaClass = new MetaService($typeHinted->name, null, 999);
			}
			
			$this->checkWakeupEvents($metaClass, $param->isOptional());
			$result[] = $metaClass;
		}

		return $result;		
	}

	/**
	 * @return array
	 */
	public function getMethodInjectedServices($class, $methodName)
	{
		$result = array();

		$refMethod = new \ReflectionMethod($class, $methodName);
		foreach (array_slice($refMethod->getParameters(), 1) as $param) {
			$metaService = $this->getMetaServiceByTypeHint($param);
			$result[] = $this->create($metaService->getClassName());
		}
		
		return $result;
	}
	
	/**
	 * @return object
	 */
	private function createExtendable($classGroup, $unqualifiedName, $pkgNamespace = null)
	{
		$pkgNamespace = $pkgNamespace ?: $this->getPackage()->getNamespace();
		$baseClass = $pkgNamespace . '\\' . $classGroup . '\\' . $unqualifiedName;
		
		$metaClass = FacadeMetadriver::findMetaClassByBaseClass($baseClass);
		
		$extendableInstance = $this->createFromMetaClass($metaClass);
		
		if ($pkgNamespace) {
			$extendableInstance->setPackage($this->getPackageByItsMember($baseClass));
		}
		
		return $extendableInstance;
	}
	
	/**
	 * Returns a package instance by the name of one of its classes
	 * 
	 * @return \PHPCrystal\PHPCrystal\Component\Package\AbstractPackage
	 */
	public function getPackageByItsMember($mixed)
	{
		$className = is_object($mixed) ? get_class($mixed) : $mixed;
		$parts = explode('\\', $className);
		$pkgNamespace = $parts[0] . '\\' . $parts[1];
		foreach (Facade\AbstractFacade::getApplication()->getExtensions(true) as $pkg) {
			if ($pkgNamespace == $pkg->getNamespace()) {
				return $pkg;
			}
		}
	}

	/**
	 * @return boolean
	 */
	private function isFullyQualifiedName($extendableName)
	{
		return 0 === strpos($extendableName, '\\\\') ? true : false;
	}
	
	/**
	 * @return boolean
	 */
	private function isQualifiedName($extendableName)
	{
		return 0 === strpos($extendableName, '\\') ? true : false;
	}
	
	/**
	 * @return boolean
	 */
	private function isUnqualifiedName($extendableName)
	{
		return $this->isFullyQualifiedName($extendableName) ||
			$this->isQualifiedName($extendableName) ? false : true;
	}
	
	/**
	 * @return object
	 */
	public function createAction($actionName)
	{
		if ($this->isUnqualifiedName($actionName)) {
			return $this->createExtendable('Action', $actionName);
		}
	}
}
