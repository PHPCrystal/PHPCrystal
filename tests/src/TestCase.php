<?php
namespace PHPCrystal\PHPCrystalTest;

use PHPCrystal\PHPCrystal\Component\Facade\AbstractClassFacade;


/**
 * @backupGlobals disabled
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
	protected $appPkg;
	protected $preserveGlobalState = false;

	
	public function __sleep()
	{
		return array();
	}

	public function setUp()
	{
		$autoloader = require (__DIR__ . '/../../vendor/autoload.php');

		$this->appPkg = Application::create($autoloader)
			->addExtension(__DIR__ . '/../..')
		;
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystalTest\Action\_Default\Account\Edit
	 */
	protected function createAccountEditAction()
	{
		AbstractClassFacade::setClassName('\\PHPCrystal\\PHPCrystalTest\\Action\\_Default\\Account\\Edit');	
		
		return AbstractClassFacade::create();
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystalTest\Action\_Default\_Default\Update
	 */
	protected function createIndexAction()
	{
		AbstractClassFacade::setClassName('\\PHPCrystal\\PHPCrystalTest\\Action\\_Default\\_Default\\Index');	
		
		return AbstractClassFacade::create();
	}	
	
	/**
	 * @return \PHPCrystal\PHPCrystalTest\Controller\_Default\_Default
	 */
	protected function createDefaultController()
	{
		AbstractClassFacade::setClassName('\\PHPCrystal\\PHPCrystalTest\\Controller\\_Default\\_Default');	
		
		return AbstractClassFacade::create();		
	}
}
