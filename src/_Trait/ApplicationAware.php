<?php
namespace PHPCrystal\PHPCrystal\_Trait;

trait ApplicationAware
{
	private $application;
	
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
}
