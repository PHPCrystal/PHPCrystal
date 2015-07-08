<?php
namespace PHPCrystal\PHPCrystal\Component\Factory\Aware;

interface ApplicationInterface
{
	public function getApplication();
	
	public function setApplication($appPkg);
}

