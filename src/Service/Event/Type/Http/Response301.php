<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\Http;

class Response301 extends AbstractRedirect
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function output()
	{
		$this->redirectCode = 301;
		parent::output();
	}
}
