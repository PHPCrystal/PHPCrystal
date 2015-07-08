<?php
namespace PHPCrystal\PHPCrystalTest\Service;

use PHPCrystal\PHPCrystal\Component\Factory\AbstractService;
use PHPCrystal\PHPCrystalTest\Export as Export;

class Foo extends AbstractService implements Export\Foo
{
	private $barService;
	
	public static function hasLazyInit()
	{
		return true;
	}

	public function __construct(Export\Bar $bar)
	{
		$this->barService = $bar;
	}
	
	public function getBarService()
	{
		return $this->barService;
	}
}
