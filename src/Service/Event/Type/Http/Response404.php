<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\Http;

class Response404 extends AbstractResponse
{
	public function __construct()
	{
		parent::__construct();
		$this->getHttpResponse()->setStatusCode(404);
	}
	
	public function output()
	{
		http_response_code(404);
		parent::output();
	}
}
