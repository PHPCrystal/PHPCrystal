<?php
// Application config container
namespace PHPCrystal\PHPCrystal\Component\Container;

use PHPCrystal\PHPCrystal\_Trait\FactoryAware;

abstract class AbstractConfig extends AbstractContainer
{
	use FactoryAware;
	
	public function init()
	{
		$this->keyPrefix = $this->getPackage()->getDotName();
	}
}
