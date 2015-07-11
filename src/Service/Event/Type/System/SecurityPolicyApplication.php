<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\System;

use PHPCrystal\PHPCrystal\Service\Event as Event;

class SecurityPolicyApplication extends Event\Type\AbstractInternal
{
	/**
	 * @var bool
	 */
	private $authRequired = false;

	/**
	 * @api
	 */
	public function __construct()
	{	
		parent::__construct();
		$this->type = Event\TYPE_UNICAST_SINGLE_DIRECTIONAL;
	}
	
	/**
	 * @return $this
	 */
	public function setAuthRequired($value)
	{
		$this->authRequired = $value;
		
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isAuthRequired()
	{
		return $this->authRequired;
	}
}
