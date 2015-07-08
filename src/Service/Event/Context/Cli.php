<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Context;

class Cli extends AbstractContext
{
	public function getEnv()
	{
		return $this->get('env');
	}
}
