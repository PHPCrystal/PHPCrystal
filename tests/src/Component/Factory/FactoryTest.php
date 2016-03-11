<?php
namespace PHPCrystal\PHPCrystalTest\Component\Factory;

use PHPCrystal\PHPCrystalTest\TestCaseDummy;

const SINGLETON_CLASS = '\\PHPCrystal\\PHPCrystal\\Service\\Metadriver\\Metadriver';

class FactoryTest extends TestCaseDummy
{
	private $appFactory;

	public function setUp()
	{
		parent::setUp();
		$this->appFactory = $this->appPkg->getFactory();
	}
	
	public function testSingleton()
	{
		$instance1 = $this->appFactory->singletonNewInstance(SINGLETON_CLASS);
		$instance2 = $this->appFactory->singletonNewInstance(SINGLETON_CLASS);
		$this->assertTrue($instance1 === $instance2);
	}

	public function testServiceCreation()
	{
		$cacheObject = $this->appFactory->create('service://phpcrystal.phpcrystal/cache');
		$this->assertInstanceOf('PHPCrystal\PHPCrystal\Service\Cache\Cache', $cacheObject);
	}
}
