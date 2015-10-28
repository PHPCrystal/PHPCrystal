<?php
namespace PHPCrystal\PHPCrystal\_Trait;

trait CreateObject
{
	public static function create(...$args)
	{
		return new static(...$args);
	}
	
	/**
	 * @return void
	 */
	public static function createArgsInArray(array $argsBlob = array())
	{
		return new static(...$argsBlob);
	}
}
