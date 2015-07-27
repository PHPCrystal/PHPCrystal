<?php
namespace PHPCrystal\PHPCrystalTest\_Trait;

use PHPCrystal\PHPCrystal\Component\Http\Request;
use PHPCrystal\PHPCrystal\Service\Event\Type\Http\Request as RequestEvent;

trait MakeRequest
{
	protected function makeRequest($fixture, &$requestEvent = null, $statusCode = 200)
	{
		$request = Request::createFromFile($fixture);
		
		$requestEvent = new RequestEvent();
		$requestEvent->setRequest($request);
		$internalEvent = $this->appPkg->dispatch($requestEvent);
		
		$this->assertEquals($statusCode, $internalEvent->getHttpResponse()->getStatusCode());

		return $internalEvent;
	}	
}
