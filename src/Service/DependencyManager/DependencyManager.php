<?php

namespace PHPCrystal\PHPCrystal\Service\DependencyManager;

use PHPCrystal\PHPCrystal\Component\Service\AbstractService,
	PHPCrystal\PHPCrystal\Component\Service\MetaService,
	PHPCrystal\PHPCrystal\Service\Metadriver\Metadriver,
	PHPCrystal\PHPCrystal\Component\Exception\System\FrameworkRuntimeError;

class DependencyManager extends AbstractService
{
	/** @var Metadriver */
	private $metadriver;

	/** @var string */
	private $rootClient;

	/** @var array */
	private $circularReferenceTracker = [];

	/**
	 * @param Metadriver $metadriver
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
	public function getInjectorDeps(\ReflectionMethod $injector)
	{
		$result = [];

		foreach ($injector->getParameters() as $param) {
			$typeHinted = $param->getClass();
			
			if ( ! $typeHinted) {
				$result[] = null;
				continue;
			}

			$typeName = $typeHinted->name;
			$depClassName = null;

			if ($typeHinted->isInterface()) {
				$metaService = $this->metadriver->getMetaServiceByInterface($typeName);
				
				// some services can be optional. deactivate service if current event
				// is not in the list of its wake-up events
				if ($param->isOptional()) {
					$this->deactivateService($metaService);
				}
				
				$depClassName = $metaService->getActiveFlag() ? $metaService->getClassName() : null;
			} else if (AbstractService::isService($typeName)) {
				$depClassName =  $typeName;
			}
			
			$result[] = $depClassName;
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

			if ($typeHinted && ($typeHinted->isInterface()
				|| $typeHinted->isInstantiable())) {

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
		
		foreach ($this->getInjectorDeps($injector) as $depClassName) {
			if ( ! $depClassName) {
				$result[] = null;
				continue;
			}

			$this->circularReferenceTracker[] = $depClassName;
			$this->circularReferenceCheck();
			
			$result[] = $this->getFactory()->createObject($depClassName,
				$this->getDependencies($this->getInjectorReflection($depClassName, $injector->name), $depth + 1));
		}
		
		return $result;
	}
}
