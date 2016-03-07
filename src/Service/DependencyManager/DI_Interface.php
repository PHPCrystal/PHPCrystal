<?php
namespace PHPCrystal\PHPCrystal\Service\DependencyManager;

use PHPCrystal\PHPCrystal\Component\Container;

interface DI_Interface
{
	/**
	 * If returns true a DependencyInjection event will be fired before class
	 * instantiation.
	 * 
	 * @return bool
	 */
	public static function fireEventUponInstantiation();
	
	/**
	 * @return array
	 */
	public static function getWakeupEvents();
}
