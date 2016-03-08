<?php

namespace PHPCrystal\PHPCrystal\Component\Factory;

use PHPCrystal\PHPCrystal\Component\Service\MetaService;
use PHPCrystal\PHPCrystal\Component\Service\AbstractService;
use PHPCrystal\PHPCrystal\Component\Service\AbstractContractor;
use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Facade\Metadriver as FacadeMetadriver;
use PHPCrystal\PHPCrystal\Component\Facade as Facade;
use PHPCrystal\PHPCrystal\Component\Exception\System\FrameworkRuntimeError;
use PHPCrystal\PHPCrystal\Component\Container as Container;
use PHPCrystal\PHPCrystal\Component\Php as Php,
	PHPCrystal\PHPCrystal\Service\DependencyManager\DI_Interface,
	PHPCrystal\PHPCrystal\Component\Factory\FactoryInterface;

const EXTENDABLE_INTERFACE = 'PHPCrystal\\PHPCrystal\\Component\\Factory\\ExtendableInterface';
const DI_INTERFACE = 'PHPCrystal\\PHPCrystal\\Service\DependencyManager\\DI_Interface';

final class Factory
{

	/**
	 * @var PHPCrystal\PHPCrystal\Component\Package\AbstractPackage
	 */
	private $package;
	private $metadriver;

	/** @var \\PHPCrystal\\PHPCrystal\\Service\\DependencyManager\\DependencyManager */
	private $DI_Manager;
	private static $singletonStorage = array();

	/**
	 * @var array
	 */
	private $dependenciesTracker;

	/**
	 * @api
	 */
	public function __construct($package)
	{
		$this->package = $package;
		$this->metadriver = $this->singletonNewInstance('\\PHPCrystal\\PHPCrystal\\Service\\Metadriver\\Metadriver')
			->setFactory($this);
		$this->DI_Manager = $this->singletonNewInstance('\\PHPCrystal\\PHPCrystal\\Service\\DependencyManager\\DependencyManager', [$this->metadriver])
			->setFactory($this);
	}

	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Package\AbstractPackage
	 */
	public static function getApplication()
	{
		return Facade\AbstractFacade::getApplication();
	}

	public function getMetaDriver()
	{
		return $this->metadriver;
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
	private function bind($newObject)
	{
		if ($newObject instanceof FactoryInterface) {
			$newObject->setFactory($this);
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
	public function singletonNewInstance($className, array $args = [])
	{
		if ($this->singletonHasInstance($className)) {
			return $this->singletonGetInstance($className);
		} else {
			$newInstance = new $className(...$args);
			self::$singletonStorage[$className] = $newInstance;
			return $newInstance;
		}
	}

	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Package\AbstractPackage
	 */
	public function getPackage()
	{
		return $this->package;
	}

	/**
	 * @return object
	 */
	public function createObject($className, $factoryArgs = [])
	{
		if ($this->singletonHasInstance($className)) {
			return $this->singletonGetInstance($className);
		}

		if (Php\Aux::implementsInterface($className, DI_INTERFACE)) {
			$injector = $this->DI_Manager->getInjectorReflection($className);
			$constructorArgs = $this->DI_Manager->getDependencies($injector);
		if (strpos($className, 'Builder')) {
		//	var_dump($this->DI_Manager->getInjectorDeps($injector)); exit;
		}			
		} else {
			$constructorArgs = $factoryArgs;
		}
				
		$newObject = $className::isSingleton() ?
			$this->singletonNewInstance($className, $constructorArgs) :
			new $className(...$constructorArgs);

		$this->bind($newObject);
		$newObject->setFactoryArgs(Container\FactoryArgs::createFromArray($factoryArgs));

		return $newObject;
	}

	/**
	 * @param string $className
	 * 
	 * @return AbstractService
	 */
	private function createService($className, $factoryArgs = [])
	{
		$service = $this->createObject($className, $factoryArgs);

		// initialize service if necessary.
		if ( ! $className::hasLazyInit()) {
			//$service->getConfig()->merge($args);
			$service->init();
		}

		return $service;
	}

	/**
	 * @return object
	 */
	public function create($className, $factoryArgs = [])
	{
		$fqcn = $this->metadriver->resolveClassName($className, $this->getPackage());
		
		if (AbstractService::isService($fqcn)) {
			return $this->createService($fqcn, $factoryArgs);
		} else {
			return $this->createObject($fqcn, $factoryArgs);
		}
	}

	/**
	 * Creates a service for the given interface
	 * 
	 * @param string $interface Interface implemented by service
	 * 
	 * @return \PHPCrystal\PHPCrystal\Component\Service\AbstractService
	 */
	public function createServiceByInterface($interface)
	{
		$metaService = $this->metadriver->getMetaServiceByInterface($interface);

		return $this->create($metaService->getClassName());
	}

	/**
	 * @return object
	 */
	public function createFromMetaClass($metaClass)
	{
		$targetClass = $metaClass->getTargetClass();
		$newInstance = $this->create('\\' . $targetClass);

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

	private function getMetaServiceByTypeHint(\ReflectionParameter $param)
	{
		$typeHinted = $param->getClass();

		if ($typeHinted->isInterface()) {
			$metaService = $this->getMetaServiceByInterface($typeHinted->name);
		} else if ($typeHinted->isInstantiable()) {
			$metaService = new MetaService($typeHinted->name, 999);
		}

		return $metaService;
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
		$pkgNamespace = $pkgNamespace ? : $this->getPackage()->getNamespace();
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
	 * @return object
	 */
	public function createAction($actionName)
	{
		if ($this->isUnqualifiedName($actionName)) {
			return $this->createExtendable('Action', $actionName);
		}
	}

}
