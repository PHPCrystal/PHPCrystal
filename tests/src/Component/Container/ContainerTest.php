<?php
namespace PHPCrystal\PHPCrystalTest;

use PHPCrystal\PHPCrystal\Component\Package\Option\Container;

class ContainerTest extends TestCase
{
	public function testHasMehtod()
	{
		$container = new Container();
		$this->assertFalse($container->has('item.key'));
		$container->set('user.name', 'Peter');
		$container->set('user.email', null);
		$this->assertTrue($container->has('user.name'));
		$this->assertTrue($container->has('user.email'));		
	}
	
	public function testIsObjectMethod()
	{
		$container = new Container();
		$container->set('now', new \DateTime('now'));
		$this->assertTrue($container->isItemObject('now'));		
	}
}
