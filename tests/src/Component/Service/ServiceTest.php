<?php
namespace PHPCrystal\PHPCrystalTest\Component\Service;

use PHPCrystal\PHPCrystalTest\TestCaseDummy,
	PHPCrystal\PHPCrystal\Facade\Cache;

class ServiceTest extends TestCaseDummy
{
	private $appFactory;
	private $cache;

	public function setUp()
	{
		parent::setUp();
		$this->cache = Cache::create();
	}
	
	public function testConfig()
	{
		//var_dump(get_class($this->cache));
	}
}
