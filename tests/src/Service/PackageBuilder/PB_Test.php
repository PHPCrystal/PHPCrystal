<?php
namespace PHPCrystal\PHPCrystalTest;

use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystalTest\Facade\Dummy;
use PHPCrystal\PHPCrystalTest\_Trait\MakeRequest;

class PB_Test extends TestCaseDummy
{
	private $builder;
	
	
	public function setUp()
	{
		parent::setUp();
		$this->builder = $this->appPkg->getBuilder();
	}
	
	public function testAssignment()
	{
		$this->assertGreaterThan(0, count($this->builder->getContractors()));
	}	
}
