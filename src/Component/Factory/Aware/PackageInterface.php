<?php
namespace PHPCrystal\PHPCrystal\Component\Factory\Aware;

interface PackageInterface
{
	public function getPackage();
	
	public function setPackage($package);
}

