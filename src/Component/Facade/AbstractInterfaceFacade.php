<?php
namespace PHPCrystal\PHPCrystal\Component\Facade;

abstract class AbstractInterfaceFacade extends AbstractFacade
{	
	protected static $interface;
	
	/**
	 * @return object
	 */
	final static public function create()
	{
		$object = self::getApplication()
			->getFactory()
			->createServiceByInterface(static::$interface);

		return $object;
	}
}
