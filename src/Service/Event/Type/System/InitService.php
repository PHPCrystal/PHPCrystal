<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\System;

use PHPCrystal\PHPCrystal\Service\Event as Event;

class InitService extends Event\Type\AbstractInternal
{
	private $serviceInstance;
	
	final public function __construct($serviceInstance)
	{
		parent::__construct();
		$this->type = Event\TYPE_BROADCAST_LEVEL_ORDER;
		$this->resultType = Event\RESULT_TYPE_SINGLE_CLOSURE;
		$this->serviceInstance = $serviceInstance;
	}
	
	/**
	 * @return boolean
	 */
	final public function hasCustomInitRoutine()
	{
		return $this->getResult() instanceof \Closure ?
			true : false;
	}
	
	/**
	 * @return
	 */
	final public function getServiceInstance()
	{
		return $this->serviceInstance;
	}
	
	/**
	 * @return string
	 */
	final public function getClassName()
	{
		return get_class($this->getServiceInstance());
	}
	
	/**
	 * @return string
	 */
	final public function getShortClassName()
	{
		$parts = explode('\\', $this->getClassName());
		
		return array_pop($parts);
	}
}
