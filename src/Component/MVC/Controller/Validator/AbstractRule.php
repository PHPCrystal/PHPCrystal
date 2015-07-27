<?php
namespace PHPCrystal\PHPCrystal\Component\MVC\Controller\Validator;

use PHPCrystal\PHPCrystal\_Trait\CreateObject;

abstract class AbstractRule
{
	use CreateObject;

	/** @var string */
	private $itemKey;


	private $errorMessage;

	/**
	 * @api
	 */
	public function __construct($itemKey)
	{
		$this->itemKey = $itemKey;
	}
	
	public function getItemKey()
	{
		return $this->itemKey;
	}
	
	/**
	 * @return mixed
	 */
	public function sanitize($value)
	{
		return $value;
	}
	
	/**
	 * @return string
	 */
	final public function getErrorMessage()
	{
		return $this->errorMessage;
	}
	
	/**
	 * @return $this
	 */
	final public function setErrorMessage($msg)
	{
		$this->errorMessage = $msg;
		
		return $this;
	}
	
	/**
	 * @return bool
	 */
	abstract public function validate($value);
}
