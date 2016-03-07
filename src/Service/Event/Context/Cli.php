<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Context;

use PHPCrystal\PHPCrystal\Service\Event\AbstractContext;

class Cli extends AbstractContext
{
	/**
	 * @return string
	 */
	public function getEnv()
	{
		return $this->get('env');
	}
	
	/**
	 * @return string
	 */
	public function getHostname()
	{
		return 'localhost';
	}
}
