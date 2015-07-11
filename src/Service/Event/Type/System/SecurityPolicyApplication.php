<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\System;

use PHPCrystal\PHPCrystal\Service\Event as Event;

class SecurityPolicyApplication extends Event\Type\AbstractInternal
{
	final public function __construct()
	{
		parent::__construct();
		$this->type = Event\TYPE_UNICAST_SINGLE_DIRECTIONAL;
		$this->instance = $newInstance;
	}
}
