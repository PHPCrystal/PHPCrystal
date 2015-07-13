<?php
namespace PHPCrystal\PHPCrystalTest\Action;

use PHPCrystal\PHPCrystalTest\TestCase;
use PHPCrystal\PHPCrystalTest\Action\_Default\_Default\Index;

class ActionTest extends TestCase
{
	public function testGetControllerName()
	{
		$this->assertEquals('_Default\_Default', Index::getControllerName());
	}
	
	public function testGetControllerClassName()
	{
		$this->assertEquals('PHPCrystal\PHPCrystalTest\Controller\_Default\_Default',
			Index::getControllerClassName());
	}
	
	public function testParseNamespace()
	{
		$action = $this->createIndexAction();
		$this->assertEquals('_Default\_Default\Index', $action->getName());
	}	
}
