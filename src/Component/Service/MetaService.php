<?php
namespace PHPCrystal\PHPCrystal\Component\Service;

class MetaService
{
	private $className;
	private $interface;
	private $priority;
	private $idle;
	
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
	 * @return boolean
	 */
	public function isIdle()
	{
		return $this->idle;
	}

	/**
	 * @return void
	 */
	public function setIdle($value)
	{
		$this->idle = (boolean)$value;
	}
}
