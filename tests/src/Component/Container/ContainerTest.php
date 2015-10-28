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
		$this->assertTrue($container->isObject('now'));		
	}

	public function testHasChanges()
	{
		$container= Container::createFromArray(['foo' => 'foo']);
		$this->assertFalse($container->hasChanges());		
		
		$container->set('foo', 'bar');
		$this->assertTrue($container->hasChanges());
		
		$container->set('bar', 'foo');
		$this->assertTrue($container->hasChanges());
	}
	
	public function testAssertTrue()
	{
		$container= Container::create();
		
		$container->set('foo', '1');
		$this->assertFalse($container->assertTrue('foo'));
		
		$container->set('bar', 1);
		$this->assertFalse($container->assertTrue('bar'));
		
		$container->set('bar', true);
		$this->assertTrue($container->assertTrue('bar'));
	}
	
	public function testPluckMethod()
	{
		$c1 = Container::create();
		$c2 = Container::create();		
		$c1->set('c2', $c2);
		$c22 = $c1->pluck('c2');
		$this->assertTrue($c22 === $c2);
	}
	
	/**
	 * @expectedException \PHPCrystal\PHPCrystal\Component\Exception\System\MethodInvocation
	 */
	public function testPluckMethod1()
	{
		$c1 = Container::create();
		$foo = $c1->pluck('foo', true);
	}
	
	public function testGetAllKeysMethod()
	{
		$c1 = Container::createFromArray(['a' => ['b' => ['c' => 1, 'cc' => 2]]]);
		$c1->set('a.d', 1);
		$this->assertEquals(3, count($c1->getAllKeys()));
		$this->assertEquals('a.b.c',$c1->getAllKeys()[0]);
		$this->assertEquals('a.b.cc',$c1->getAllKeys()[1]);		
		$this->assertEquals('a.d', $c1->getAllKeys()[2]);
		$c1->set('a.b', 1);
		$this->assertEquals(2, count($c1->getAllKeys()));		
	}
}
