<?php
namespace PHPCrystal\PHPCrystalTest;

use PHPCrystal\PHPCrystal\Component\Filesystem\PathResolver;

class PathResolverTest extends TestCase
{
	public function testBulk()
	{
		PathResolver::addAlias('current', __DIR__);		
		$path = PathResolver::create('@current', basename(__FILE__));
		$this->assertEquals(__FILE__, $path->toString());
	}
	
	/**
	 * @expectedException \RuntimeException
	 */
	public function testCircularReference()
	{
		PathResolver::addAlias('foo', '@bar');
		PathResolver::addAlias('bar', '@baz');
		PathResolver::addAlias('baz', '@foo');
		$path = PathResolver::create('@foo');
		$path->toString();
	}
	
	/**
	 * @expectedException \RuntimeException
	 */
	public function testUndefinedAlias()
	{
		$path = PathResolver::create('@ziggy');		
		$path->toString();
	}
	
	public function testAppAlias()
	{
		$appPath = PathResolver::create('@app');
		$this->assertEquals(realpath(__DIR__ . '/../../../'), $appPath->toString());
	}
}
