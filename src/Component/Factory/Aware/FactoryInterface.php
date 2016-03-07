<?php
namespace PHPCrystal\PHPCrystal\Component\Factory\Aware;

use PHPCrystal\PHPCrystal\Component\Factory\Factory;

interface FactoryInterface
{
	public function getFactory();
	
	public function setFactory(Factory $factory);
}
