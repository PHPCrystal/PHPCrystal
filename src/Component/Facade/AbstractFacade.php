<?php
namespace PHPCrystal\PHPCrystal\Component\Facade;

abstract class AbstractFacade
{
	static protected $appPkg;
	
	/**
	 * Return the name of a class implementing giving facade
	 * 
	 * @return string|null
	 */
	protected static function getServiceClass() {  }
	
	/**
	 * @return string|null
	 */
	protected static function getServiceInterface() {  }
	
	/**
	 * @return mixed
	 */
	final public static function __callStatic($name, $args)
	{
		$object = static::create();
		
		return $object->{$name}(...$args);
	}
	
	/**
	 * @return PHPCrystal\PHPCrystal\Component\Package\AbstractApplication
	 */
	final public static function getApplication()
	{
		return self::$appPkg;
	}

	/**
	 * @return void
	 */
	final public static function setApplication($appPkg)
	{
		self::$appPkg = $appPkg;
	}
}
