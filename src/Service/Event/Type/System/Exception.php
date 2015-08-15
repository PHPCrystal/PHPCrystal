<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\System;

use PHPCrystal\PHPCrystal\Service\Event as Event;

class Exception extends Event\Type\AbstractInternal
{
	private $instance;
	
	final public function __construct($newInstance)
	{
		parent::__construct();
		$this->type = Event\TYPE_UNICAST_BIDIRECTIONAL;
		$this->instance = $newInstance;
	}
}
