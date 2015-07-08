<?php
namespace PHPCrystal\PHPCrystal\Component\Facade;

abstract class AbstractClassFacade extends AbstractFacade
{	
	protected static $className;
	
	/**
	 * @return object
	 */
	final public static function create()
	{
		$object = self::$appPkg->getFactory()
			->create(static::$className);
		
		return $object;
	}
	
	/**
	 * @return void
	 */
	final public static function setClassName($className)
	{
		self::$className = $className;
	}
}
