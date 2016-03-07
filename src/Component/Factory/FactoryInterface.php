<?php
namespace PHPCrystal\PHPCrystal\Component\Factory;

interface FactoryInterface
{
	/**
	 * @return Factory
	 */
	public function getFactory();
	
	/**
	 * @return void
	 */
	public function setFactory(Factory $factory);
	
	/**
	 * @return void
	 */
	public function init();
	
	/**
	 * @return bool
	 */
	public static function isSingleton();
}
