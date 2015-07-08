<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\Http;

use PHPCrystal\PHPCrystal\Service\Event\Type as EventType;

class Response500 extends EventType\Http\AbstractResponse
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function output()
	{
		;
	}
}
