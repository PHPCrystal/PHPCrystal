<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\Http;

class Response401 extends AbstractResponse
{
	public function __construct()
	{
		parent::__construct();
		$this->getHttpResponse()->setStatusCode(401);
	}
	
	public function output()
	{
		http_response_code(401);
		parent::output();
	}
}