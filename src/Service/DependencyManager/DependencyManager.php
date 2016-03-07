<?php

namespace PHPCrystal\PHPCrystal\Service\DependencyManager;

use PHPCrystal\PHPCrystal\Component\Service\AbstractService;
use PHPCrystal\PHPCrystal\Component\Service\MetaService;
use PHPCrystal\PHPCrystal\Service\Metadriver\Metadriver;

const DEPENDENCY_INJECTOR_MAGIC_METHOD = '__DI_injector';

class DependencyManager extends AbstractService
{

	/** @var Metadriver */
	private $metadriver;

	/** @var string */
	private $rootClient;

	/** @var array */
	private $circularReferenceTracker = [];

	/**
	 * @api
	 */
	public function __construct(Metadriver $metadriver)
	{
		parent::__construct();
		$this->metadriver = $metadriver;
	}

	/**
	 * @return void
	 */
	private function circularReferenceCheck()
	{
		if (count(array_unique($this->circularReferenceTracker)) != count($this->circularReferenceTracker)) {
			FrameworkRuntimeError::create('A circular dependency for the class "%s" has been detected', null, $this->rootClient)
				->_throw();
		}
	}

	/**
	 * @return void
	 */
	private function deactivateService($meta)
	{
		$serviceClass = $meta->getClassName();
		if ( ! $serviceClass::getWakeupEvents()) {
			$meta->setActiveFlag(false);
			return;
		}

		$currentEvent = $this->getApplication()
			->getCurrentEvent();

		foreach ($serviceClass::getWakeupEvents() as $wakeupEvent) {
			if ($currentEvent instanceof $wakeupEvent) {
				$meta->setActiveFlag(true);
				return;
			}
		}

		$meta->setActiveFlag(false);
	}
	
	/**
	 * @return array
	 */
	private function getInjectorMetaServices(\ReflectionMethod $injector)
	{
		$result = [];
		
		foreach ($injector->getParameters() as $param) {
			$typeHinted = $param->getClass();
			$typeName = $typeHinted->name;

			if ($typeHinted->isInterface()) {
				$metaService = $this->metadriver->getMetaServiceByInterface($typeName);
			} else if ($typeHinted->isInstantiable() && $typeName instanceof AbstractService) {
				$metaService = new MetaService($typeName, null, $this->getPackage()->getPriority());
			} else {
				$result[] = null;
				continue;
			}

			// some services can be optional. deactivate service if current event
			// is not in the list of its wake-up events
			if ($param->isOptional()) {
				$this->deactivateService($metaService);
			}
			
			$result[] = $metaService->getActiveFlag() ? $metaService : null;
		}
		
		return $result;
	}

	/**
	 * @return bool
	 */
	public function hasDependencies($clientClass, $injector)
	{
		$refMethod = new \ReflectionMethod($clientClass, $injector);

		foreach ($refMethod->getParameters() as $param) {
			$typeHinted = $param->getClass();
			if ($typeHinted && ($typeHinted->isInterface() || $typeHinted->isInstantiable())) {
				return true;
			}
		}

		return false;
	}
	
	/**
	 * @return \ReflectionMethod
	 */
	public function getInjectorReflection($client, $injector = '__construct')
	{
		return new \ReflectionMethod($client, $injector);
	}

	/**
	 * @return array
	 */
	public function getDependencies(\ReflectionMethod $injector, $depth = 0)
	{
		$result = [];
		
		if ($depth == 0) {
			$this->circularReferenceTracker = [];
			$this->rootClient = $injector->class;
		}		
		
		foreach ($this->getInjectorMetaServices($injector) as $metaService) {
			if ( ! $metaService) {
				$result[] = null;
				continue;
			}

			$depClassName = $metaService->getClassName();
			$this->circularReferenceTracker[] = $depClassName;
			$this->circularReferenceCheck();
			
			$result[] = $this->getFactory()->createObject($depClassName,
				$this->getDependencies($this->getInjectorReflection($depClassName, $injector->name), $depth + 1));
		}
		
		return $result;
	}
}
