<?php
namespace PHPCrystal\PHPCrystalTest\Service\Dummy;

use PHPCrystal\PHPCrystal\Component\Service\AbstractService;

class Dummy extends AbstractService
{
	private $pkgName;
	private $sentence = 'blah blah blah';
	
	public function saySomething()
	{
		return $this->sentence;
	}
	
	public function getPackageName()
	{
		return $this->pkgName;
	}
}
