<?php
namespace PHPCrystal\PHPCrystalTest\Service\Dummy;

use PHPCrystal\PHPCrystal\Component\Factory\AbstractService;
use PHPCrystal\PHPCrystalTest\Export as Export;

class Bar extends AbstractService implements Export\Bar
{
	public static function isSingleton()
	{
		return true;
	}
	
	public function getServiceName()
	{
		return 'Bar';
	}
}
