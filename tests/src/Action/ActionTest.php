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
	
	public function testAccountEditAction()
	{
		$this->makeRequest('@app/fixture/http_request/post_account_edit.txt');

		$router = $this->appPkg->getRouter();
		$action = $router->getAction();
		$this->assertInstanceOf('PHPCrystal\PHPCrystalTest\Action\_Default\Account\Edit', $action);

		$uriString = $action->getReverseURI(658263);
		$this->assertEquals('/user/658263/edit/', $uriString);
		
		$ctrlAnnot = $action->getExtendableInstance()->getControllerMethodAnnotation();
		$this->assertEquals('editUserProfileAction', $ctrlAnnot->getMethodName());
	}
}
