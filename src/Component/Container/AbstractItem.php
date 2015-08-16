<?php
namespace PHPCrystal\PHPCrystal\Component\Container;

abstract class AbstractItem
{
	private $key;
	private $value;

	/**
	 * @api
	 */
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

	/**
	 * @return void
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * @return mixed
	 */
	public function __toString()
	{
		return $this->getValue();
	}

	/**
	 * Wrapper for the ::__toString method
	 * 
	 * @return mixed
	 */
	public function toString()
	{
		return $this->__toString();
	}
}
