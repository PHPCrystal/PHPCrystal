<?php
namespace PHPCrystal\PHPCrystalTest\Service;

use PHPCrystal\PHPCrystal\Component\Factory\AbstractService;
use PHPCrystal\PHPCrystalTest\Export as Export;

class AnotherBar extends AbstractService implements Export\Bar
{
	public function getServiceName()
	{
		return 'AnotherBar';
	}
}
