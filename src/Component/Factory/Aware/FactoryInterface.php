<?php
namespace PHPCrystal\PHPCrystal\Component\Factory\Aware;

interface FactoryInterface
{
	public function getFactory();
	
	public function setFactory(\PHPCrystal\PHPCrystal\Component\Factory\Factory $factory);
}
