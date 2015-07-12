<?php
namespace PHPCrystal\PHPCrystalTest;

use PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper;

class FileHelperTest extends TestCase
{
	public function testBulk()
	{
		FileHelper::addAlias('current', __DIR__);		
		$path = FileHelper::create('@current', basename(__FILE__));
		$this->assertEquals(__FILE__, $path->toString());
	}
	
	/**
	 * @expectedException \RuntimeException
	 */
	public function testCircularReference()
	{
		FileHelper::addAlias('foo', '@bar');
		FileHelper::addAlias('bar', '@baz');
		FileHelper::addAlias('baz', '@foo');
		$path = FileHelper::create('@foo');
		$path->toString();
	}
	
	/**
	 * @expectedException \RuntimeException
	 */
	public function testUndefinedAlias()
	{
		$path = FileHelper::create('@ziggy');		
		$path->toString();
	}
	
	public function testAppAlias()
	{
		$appPath = FileHelper::create('@app');
		$this->assertEquals(realpath(__DIR__ . '/../../../'), $appPath->toString());
	}
}
