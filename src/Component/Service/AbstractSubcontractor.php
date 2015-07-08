<?php
namespace PHPCrystal\PHPCrystal\Component\Service;

abstract class AbstractSubcontractor extends AbstractService
{
	/**
	 * @return boolean
	 */
	public static function hasLazyInit()
	{
		return false;
	}
	
	/**
	 * @return boolean
	 */
	public static function isSingleton()
	{
		return true;
	}
}
