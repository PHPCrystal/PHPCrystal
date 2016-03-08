<?php
namespace PHPCrystal\PHPCrystalTest\Service\DependencyManager;

use PHPCrystal\PHPCrystalTest\TestCaseDummy,
	PHPCrystal\PHPCrystal\Facade\Cache;

class DependencyManagerTest extends TestCaseDummy
{
	public function testServiceInjection()
	{
		$cache = Cache::create();
		$this->assertInstanceOf('\\PHPCrystal\\PHPCrystalTest\\Service\\Cache\\Cache', $cache);
	}
}
