<?php
namespace PHPCrystal\PHPCrystalTest\Action;

use PHPCrystal\PHPCrystalTest\TestCase;
use PHPCrystal\PHPCrystalTest\Action\_Default\_Default\Index;
use PHPCrystal\PHPCrystalTest\_Trait\MakeRequest;
use PHPCrystal\PHPCrystal\Component\Exception as Exception;

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
		$this->assertEquals('/account/658263/edit/', $uriString);
		
		$ctrlAnnot = $action->getExtendableInstance()->getControllerMethodAnnotation();
		$this->assertEquals('editUserProfileAction', $ctrlAnnot->getMethodName());
	}
	
	public function testDefaultRouteParam()
	{
		$this->makeRequest('@app/fixture/http_request/get_account');
		$currEvent = $this->appPkg->getCurrentEvent();
		$uriInput = $currEvent->getRequest()->getURIInput();
		$this->assertEquals('master', $uriInput->get('default_param'));
	}

	public function testDefaultRouteParam2()
	{
		$this->makeRequest('@app/fixture/http_request/get_account_8627');
		$currEvent = $this->appPkg->getCurrentEvent();
		$uriInput = $currEvent->getRequest()->getURIInput();
		$this->assertEquals(8627, $uriInput->get('default_param'));
	}
	
	/**
	 * @expectedException PHPCrystal\PHPCrystal\Component\Exception\Security\AuthRequired
	 */
	public function testAuthentication()
	{
		Exception\AbstractException::$nonMaskable = true;
		$this->makeRequest('@app/fixture/http_request/edit_account_1_no_sid');		
	}
}
