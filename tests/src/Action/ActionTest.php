<?php
namespace PHPCrystal\PHPCrystalTest\Action;

use PHPCrystal\PHPCrystalTest\TestCase;
use PHPCrystal\PHPCrystalTest\Action\_Default\_Default\Index;
use PHPCrystal\PHPCrystalTest\_Trait\MakeRequest;

class ActionTest extends TestCase
{
	use MakeRequest;
	
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
	
	public function testAccountEditAction()
	{
		$this->makeRequest(__DIR__ . '/../../fixture/http_request/post_account_edit.txt');
		//$editAction = $this->createAccountEditAction();
	}
}
