<?php
namespace PHPCrystal\PHPCrystal\_Trait;

trait CreateObject
{
	public static function create(...$args)
	{
		return new static(...$args);
	}
}
