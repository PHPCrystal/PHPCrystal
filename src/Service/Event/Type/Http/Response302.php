<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\Http;

class Response302 extends AbstractRedirect
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function output()
	{
		$this->redirectCode = 302;
		parent::output();
	}
}
