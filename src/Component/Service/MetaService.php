<?php
namespace PHPCrystal\PHPCrystal\Component\Service;

class MetaService
{
	/** @var string */
	private $className;
	/** @var string */
	private $interface;
	/** @var integer */
	private $priority;
	/** @var bool */
	private $isActive = true;

	/**
	 * Constructor
	 */
	public function __construct($className, $interface, $priority)
	{
		$this->className = $className;
		$this->interface = $interface;
		$this->priority = $priority;
	}
	
	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}
	
	/**
	 * @return string
	 */
	public function getInterface()
	{
		return $this->interface;
	}
	
	/**
	 * @return integer
	 */
	public function getPriority()
	{
		return $this->priority;
	}
	
	/**
	 * @return bool
	 */
	public function getActiveFlag()
	{
		return $this->isActive;
	}

	/**
	 * @return void
	 */
	public function setActiveFlag($flag)
	{
		$this->isActive = $flag;
	}
}
