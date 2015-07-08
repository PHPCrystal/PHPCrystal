<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\System;

use PHPCrystal\PHPCrystal\Service\Event as Event;

class NewInstance extends Event\Type\AbstractInternal
{
	private $instance;
	
	final public function __construct($newInstance)
	{
		parent::__construct();
		$this->type = Event\TYPE_BROADCAST_LEVEL_ORDER;
		$this->instance = $newInstance;
	}
	
	/**
	 * @return object
	 */
	final public function getInstance()
	{
		return $this->instance;
	}
	
	/**
	 * @return boolean
	 */
	final public function isService()
	{
		return Container::isService($this->instance);
	}
}
