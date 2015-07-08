<?php
namespace PHPCrystal\PHPCrystal;

use PHPCrystal\PHPCrystal\Component\Package as Package;

class Extension extends Package\AbstractExtension
{	
	public static function create()
	{
		return new static();
	}
	
	public function __construct()
	{
		parent::__construct();
		$this->setRouter('\\PHPCrystal\\PHPCrystal\\Service\\Router\\Utility');
	}	
}
