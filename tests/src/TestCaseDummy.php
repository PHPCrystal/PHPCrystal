<?php
namespace PHPCrystal\PHPCrystalTest;

use PHPCrystal\PHPCrystal\Service\Event as Event;

/**
 * @backupGlobals disabled
 */
class TestCaseDummy extends TestCase
{
	public function setUp()
	{
		$autoloader = require (__DIR__ . '/../../vendor/autoload.php');

		$this->appPkg = Application::create($autoloader)
			->addExtension(__DIR__ . '/../..')
			->bootstrap(Event\Type\Dummy::create())
		;
	}
}
