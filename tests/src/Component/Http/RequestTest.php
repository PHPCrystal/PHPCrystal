<?php
namespace PHPCrystal\PHPCrystalTest;

use PHPCrystal\PHPCrystal\Component\Http\Request;
use PHPCrystal\PHPCrystal\Service\Event\Type\Http\Request as RequestEvent;

class RequestTest extends TestCase
{
	protected function makeRequest($reqBodyFilename, &$requestEvent = null)
	{
		$request = Request::createFromFile($reqBodyFilename);
		$requestEvent = new RequestEvent();
		$requestEvent->setRequest($request);
		$internalEvent = $this->appPkg->dispatch($requestEvent);
		
		return $internalEvent;
	}
	
	public function testControllerOutput()
	{
		$requestEvent = null;
		$this->makeRequest('@app/fixture/http_request/get_index',
			$requestEvent);
		$this->assertEquals('Unit tests rock!', $requestEvent->getResult());		
	}
	
	public function testResponse200()
	{
		$internalEvent = $this->makeRequest('@app/fixture/http_request/get_index');		
		$this->assertEquals(200, $internalEvent->getHttpResponse()->getStatusCode());
	}
	
	public function testResponse404()
	{
		$internalEvent = $this->makeRequest('@app/fixture/http_request/get_404.txt');
		$this->assertEquals(404, $internalEvent->getHttpResponse()->getStatusCode());		
	}
	
	public function testPostRequest()
	{
		$requestEvent = null;
		$internalEvent = $this->makeRequest('@app/fixture/http_request/post_1.txt', $requestEvent);
		$request = $requestEvent->getRequest();
		$postInput = $request->getPostInput();
		$this->assertEquals('vasiliy.pupkin@gmail.com', $postInput->get('email'));
	}
}
