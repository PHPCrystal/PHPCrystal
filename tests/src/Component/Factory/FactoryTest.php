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

	public function _testCreateExtendable()
	{
		$action = $this->appPkg->getFactory()
			->create("Action\_Default\_Default\Index");
		$this->assertInstanceOf("PHPCrystal\\PHPCrystal\\Component\\MVC\\Controller\\Action\\AbstractAction", $action);
	}
	
//	public function testGetPackageByItsMemeber()
//	{
////		$pkgInstance = $this->appPkg->getFactory()
////			->getPackageByItsMember($this->createDefaultController());
////		$this->assertInstanceOf('PHPCrystal\\PHPCrystal\\Component\\Package\\AbstractPackage', $pkgInstance);
////		$this->assertEquals('phpcrystal/phpcrystaltest', $pkgInstance->getComposerName());
//	}
}
