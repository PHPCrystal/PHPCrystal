<?php
namespace PHPCrystal\PHPCrystal\Component\Factory\Aware;

interface DependencyInjectionInterface
{
	/**
	 * If returns true a DependencyInjection event will be fired before class
	 * instantiation.
	 * 
	 * @return boolean
	 */
	public static function fireEventUponInstantiation();
}
