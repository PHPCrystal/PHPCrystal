<?php
namespace PHPCrystal\PHPCrystalTest;

class FactoryTest extends TestCase
{
	public function testDependencyInjection()
	{
		$this->assertInstanceOf('\\PHPCrystal\\PHPCrystal\\Contract\\Cache',
			$this->createDefaultController()->getCache());
	}
	
	public function testCreateExtendable()
	{
		$action = $this->appPkg->getFactory()
			->createAction('_Default\\_Default\\Index');
		$this->assertInstanceOf("PHPCrystal\\PHPCrystal\\Component\\MVC\\Controller\\Action\\AbstractAction", $action);
	}
	
	public function testGetPackageByItsMemeber()
	{
		$pkgInstance = $this->appPkg->getFactory()
			->getPackageByItsMember($this->createDefaultController());
		$this->assertInstanceOf('PHPCrystal\\PHPCrystal\\Component\\Package\\AbstractPackage', $pkgInstance);
		$this->assertEquals('phpcrystal/phpcrystaltest', $pkgInstance->getComposerName());
	}
}
