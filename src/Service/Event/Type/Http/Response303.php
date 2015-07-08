<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\Http;

class Response303 extends AbstractRedirect
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function output()
	{
		$this->redirectCode = 303;
		parent::output();
	}
}
