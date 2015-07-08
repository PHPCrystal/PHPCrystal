<?php
namespace PHPCrystal\PHPCrystal\Component\Container;

abstract class AbstractItem
{
	private $key;
	private $value;
	
	public function __construct($key, $value)
	{
		$this->key = $key;
		$this->value = $value;
	}
	
	/**
	 * @return string
	 */
	final public function getKey()
	{
		return $this->name;
	}
	
	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}
	
	public function setValue($value)
	{
		$this->value = $value;
	}
	
	public function __toString()
	{
		return $this->getValue();
	}
	
	public function toString()
	{
		return $this->__toString();
	}
}
