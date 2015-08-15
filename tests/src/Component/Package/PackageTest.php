<?php
namespace PHPCrystal\PHPCrystalTest\Action;

use PHPCrystal\PHPCrystalTest\TestCase;
use PHPCrystal\PHPCrystalTest\Action\_Default\_Default\Index;
use PHPCrystal\PHPCrystalTest\_Trait\MakeRequest;
use PHPCrystal\PHPCrystal\Component\Exception as Exception;

class PackageTest extends TestCase
{
	use MakeRequest;

	public function testConfig()
	{
		$this->makeRequest('@app/fixture/http_request/get_index');
		$config = $this->appPkg->getConfig();
	}	
}
