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
		$this->makeRequest('@app/fixture/http_request/get_index.txt');		
		$session = Session::create();
		$session->init();
		$session->set('foo', 123);
		$session->finish();
	}
	
	public function testGetter()
	{
		$this->makeRequest('@app/fixture/http_request/get_index.txt');
		$session = Session::create();
		$session->init();
		$this->assertEquals(123, $session->get('foo'));
		$session->finish();
	}	
}
