<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\System;

use PHPCrystal\PHPCrystal\Service\Event as Event;

class SecurityPolicyApplication extends Event\Type\AbstractInternal
	implements Event\Type\MergeableInterface
{
	/**
	 * @var bool
	 */
	private $authRequired = false;
	private $csrfTokenRequired = false;

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
	
	/**
	 * @return bool
	 */
	public function isCsrfTokenRequired()
	{
		return $this->csrfTokenRequired;
	}
	
	/**
	 * @return void
	 */
	public function merge($event)
	{
		$this->authRequired = $event->authRequired;
		$this->csrfTokenRequired = $event->csrfTokenRequired;
	}
}
