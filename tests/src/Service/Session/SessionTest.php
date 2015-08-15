<?php
namespace PHPCrystal\PHPCrystalTest\Service\Session;

use PHPCrystal\PHPCrystalTest\TestCase;
use PHPCrystal\PHPCrystalTest\Facade\Session;
use PHPCrystal\PHPCrystalTest\_Trait\MakeRequest;

class SessionTest extends TestCase
{
	use MakeRequest;

	public function testSetter()
	{
		$this->makeRequest(__DIR__ . '/../../../fixture/http_request/get_index');		
		$session = Session::create();
		$session->init();
		$session->set('foo', 123);
		$session->finish();
	}
	
	public function testGetter()
	{
		$this->makeRequest(__DIR__ . '/../../../fixture/http_request/get_index');
		$session = Session::create();
		$session->init();
		$this->assertEquals(123, $session->get('foo'));
		$session->finish();
	}	
}
