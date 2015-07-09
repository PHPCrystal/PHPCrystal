<?php
namespace PHPCrystal\PHPCrystal\Component\Service;

use PHPCrystal\PHPCrystal\Component\Factory\Aware as Aware;
use PHPCrystal\PHPCrystal\_Trait\AFPAware;

abstract class AbstractService implements
	Aware\ApplicationInterface,
	Aware\FactoryInterface,
	Aware\PackageInterface,
	Aware\DependencyInjectionInterface
{
	use AFPAware;
	
	/**
	 * @var boolean
	 */
	protected $isInitialized = false;
	
	/**
	 * @var \Closure
	 */
	private $customInitClosure;
	
	
	public function __construct() { }
	
	/**
	 * By default all services do not fire DI event.
	 * 
	 * {@inheritdoc}
	 */
	public static function fireEventUponInstantiation()
	{
		return false;
	}
	
	/**
	 * @return array
	 */
	public static function getWakeupEvents()
	{
		return array();
	}
	
	/**
	 * @return boolean
	 */
	public static function hasLazyInit()
	{
		return false;
	}
	
	/**
	 * @return boolean
	 */
	public static function isSingleton()
	{
		return false;
	}
	
	/**
	 * @return boolean
	 */
	final public function isInitialized()
	{
		return $this->isInitialized;
	}
	
	/**
	 * @return bool
	 */
	final static public function isService($className)
	{
		return is_subclass_of($className, __CLASS__);		
	}

	/**
	 * @return null
	 */
	final public function setCustomInitClosure(\Closure $closure)
	{
		$this->customInitClosure = $closure;
	}
	
	/**
	 * @return void
	 */
	final public function customInit()
	{
		if ($this->customInitClosure instanceof \Closure) {
			$customInitClosure = $this->customInitClosure->bindTo($this, $this);
			$customInitClosure();
		}
	}

	/**
	 * @return null
	 */
	public function init()
	{
		$this->customInit();
	}
	
	/**
	 * @return string
	 */
	final public function getNamespace()
	{
		$refClass = new \ReflectionClass($this);
		
		return $refClass->getNamespaceName();
	}
}
