<?php
namespace PHPCrystal\PHPCrystal\Component\MVC\Controller\Validator;

use PHPCrystal\PHPCrystal\_Trait\CreateObject;

abstract class AbstractRule
{
	use CreateObject;

	private $errorMessage;

	public function __construct()
	{
		
	}
	
	public function sanitize($value)
	{
		return $value;
	}
	
	final public function getErrorMessage()
	{
		return $this->errorMessage;
	}
	
	final public function setErrorMessage($msg)
	{
		$this->errorMessage = $msg;
		
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	abstract public function validate($value);
}