<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\System;

use PHPCrystal\PHPCrystal\Service\Event as Event;

class DependencyInjection extends Event\Type\AbstractInternal
{
	private $targetClass;
	private $dependencies;
	
	final public function __construct($targetClassName, $depsArray)
	{
		parent::__construct();
		$this->type = Event\TYPE_BROADCAST_LEVEL_ORDER;
		$this->targetClass = $targetClassName;
		$this->dependencies = $depsArray;
	}
	
	/**
	 * @return string
	 */
	public function getTargetClass()
	{
		return $this->targetClass;
	}

	/**
	 * @return array
	 */
	final public function getDependencies()
	{
		return $this->dependencies;
	}
}
