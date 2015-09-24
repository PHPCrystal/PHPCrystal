<?php
namespace PHPCrystal\PHPCrystal\Component\Http\Response\Header;

use PHPCrystal\PHPCrystal\_Trait\CreateObject,
	PHPCrystal\PHPCrystal\Component\Exception\System\FrameworkRuntimeError;

abstract class AbstractField
{
	use CreateObject;
	
	protected static $storage = [];
	
	/**
	 * @return void
	 */
	protected function getName()
	{
		FrameworkRuntimeError::create('Class "%s" must override method "%s"',
			null, static::class, __METHOD__)
			->_throw()
		;
	}

	/**
	 * By default only one filed value is allowed 
	 * 
	 * @return string
	 */
	protected function getKey()
	{
		return get_class($this);
	}
	
	/**
	 * AbstractField contructor
	 * 
	 */
	public function __construct()
	{
		
	}

	/**
	 * @return array
	 */
	public static function getAll()
	{
		return self::$storage;
	}

	/**
	 * @todo throw exception
	 * 
	 * @return void
	 */
	final public function save()
	{
		$key = $this->getKey();
		if (isset(self::$storage[$key])) {
			FrameworkRuntimeError::create('HTTP response-header field "%s" is already specified',
				null, $this->getName())
				->_throw()
			;
		} else  {
			self::$storage[$key] = $this;
		}
	}

	abstract public function output();
}
