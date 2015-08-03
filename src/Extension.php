<?php
namespace PHPCrystal\PHPCrystal;

use PHPCrystal\PHPCrystal\Component\Package as Package;
use PHPCrystal\PHPCrystal\Service\Event as Event;

class Extension extends Package\AbstractExtension
{	
	public static function create()
	{
		return new static();
	}
}
