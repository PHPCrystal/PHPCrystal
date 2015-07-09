<?php
namespace PHPCrystal\PHPCrystalTest\Component\Container;

use PHPCrystal\PHPCrystal\Component\Package\Option\Container;
use PHPCrystal\PHPCrystalTest\TestCase;

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
	
	public function testHasChanges()
	{
		$container= Container::create(null, ['foo' => 'foo']);
		$container->set('foo', 'bar');
		$this->assertFalse($container->hasChanges());
		$container->set('bar', 'foo');
		$this->assertTrue($container->hasChanges());
	}
}
