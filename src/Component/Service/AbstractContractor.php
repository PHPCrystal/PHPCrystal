<?php
namespace PHPCrystal\PHPCrystal\Component\Service;

abstract class AbstractContractor extends AbstractService
{
	/**
	 * @return boolean
	 */
	public static function hasLazyInit()
	{
		return true;
	}
	
	/**
	 * @return boolean
	 */
	public static function isSingleton()
	{
		return true;
	}

	/**
	 * @return bool
	 */
	final static public function isContractor($className)
	{
		return is_subclass_of($className, __CLASS__);
	}

	/**
	 * @return string|null
	 */
	public static function getContract($className, array $contracts)
	{
		foreach (class_implements($className) as $interface) {
			if (in_array($interface, $contracts)) {
				return $interface;
			}
		}
	}
}
