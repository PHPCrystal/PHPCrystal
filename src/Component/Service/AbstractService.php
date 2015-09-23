<?php
namespace PHPCrystal\PHPCrystal\Component\Service;

use PHPCrystal\PHPCrystal\Component\Factory\Aware as Aware;
use PHPCrystal\PHPCrystal\Facade\Metadriver;
use PHPCrystal\PHPCrystal\_Trait\AFPAware;
use PHPCrystal\PHPCrystal\_Trait\PkgConfigAware;
use PHPCrystal\PHPCrystal\Component\Exception\System\FrameworkRuntimeError;

abstract class AbstractService implements
	Aware\ApplicationInterface,
	Aware\FactoryInterface,
	Aware\PackageInterface,
	Aware\DependencyInjectionInterface
{
	use AFPAware, PkgConfigAware;
	
	/**
	 * @var boolean
	 */
	protected $isInitialized = false;

	/**
	 * Service short name
	 * 
	 * @var string
	 */
	protected $shortName;
	
	/**
	 * @var \Closure
	 */
	private $customInitClosure;

	protected $serviceConfig;
	
	/**
	 * @api
	 */
	public function __construct()
	{
		// Default short name 
		$this->shortName = strtolower((new \ReflectionClass($this))->getShortName());
	}

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
	 * @return string
	 */
	public function getName()
	{		
		$ownerInstance = Metadriver::getOwnerInstance($this);
		$fullName = $ownerInstance->getComposerName(true) . '.' . $this->getShortName();

		return $fullName;
	}
	
	/**
	 * @return string
	 */
	public function getShortName()
	{
		return $this->shortName;
	}

	/**
	 * Gets service configuration container
	 * 
	 * @return \PHPCrystal\PHPCrystal\Component\Package\Config
	 * 
	 * @throws \PHPCrystal\PHPCrystal\Component\Exception\System\MethodInvocation
	 *     if configuration container is not found
	 */
	public function getServiceConfig()
	{
		return $this->getMergedConfig()
			->pluck($this->getName(), true);
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
	
	/**
	 * @return bool
	 */
	final protected function validateServiceName($serviceName)
	{
		$nameComponent = '[\w\d_-]+';

		return preg_match("/^{$nameComponent}\.{$nameComponent}\.{$nameComponent}$/", $serviceName);		
	}
}
