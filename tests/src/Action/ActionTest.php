<?php
namespace PHPCrystal\PHPCrystalTest\Action;

use PHPCrystal\PHPCrystalTest\TestCase;

class ActionTest extends TestCase
{
	public function testParseNamespace()
	{
		$action = $this->createIndexAction();
		$this->assertEquals('_Default\_Default\Index', $action->getName());
	}	
}
