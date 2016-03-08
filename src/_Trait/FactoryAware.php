<?php
namespace PHPCrystal\PHPCrystal\_Trait;

use PHPCrystal\PHPCrystal\Component\Container\FactoryArgs,
	PHPCrystal\PHPCrystal\Component\Factory\Factory;

trait FactoryAware
{
	private $factory;

	/** @var array */
	private $factoryArgs;

	/**
	 * @return PHPCrystal\PHPCrystal\Component\Factory\Factory
	 */
	public function getFactory()
	{
		return $this->factory;
	}

	/**
	 * @return $this
	 */
	public function setFactory(Factory $factory)
	{
		$this->factory = $factory;
		
		return $this;
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Package\AbstractPackage
	 */
	public function getPackage()
	{
		return $this->getFactory()->getPackage();
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Package\AbstractApplication
	 */
	public function getApplication()
	{
		return $this->getFactory()->getApplication();
	}
	
	/**
	 * @return array
	 */
	public function getFactoryArgs()
	{
		return $this->factoryArgs;
	}
	
	/**
	 * @return void
	 */
	public function setFactoryArgs(FactoryArgs $args)
	{
		$this->factoryArgs = $args;
	}
}
