<?php
namespace PHPCrystal\PHPCrystal\_Trait;

trait AFPAware
{
	private $application;
	private $factory;
	private $package;
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Package\AbstractApplication
	 */
	final public function getApplication()
	{
		return $this->application;
	}
	
	/**
	 * @return void
	 */
	final public function setApplication($application)
	{
		$this->application = $application;
	}

	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Factory\Factory
	 */
	final public function getFactory()
	{
		return $this->factory;
	}

	/**
	 * @return void
	 */
	final public function setFactory(\PHPCrystal\PHPCrystal\Component\Factory\Factory $factory)
	{
		$this->factory = $factory;
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Package\AbstractPackage
	 */
	final public function getPackage()
	{
		return $this->package;
	}
	
	/**
	 * @return void
	 */
	final public function setPackage($package)
	{
		$this->package = $package;
	}
}
