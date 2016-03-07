<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Context;

use PHPCrystal\PHPCrystal\Service\Event\AbstractContext;

class Dummy extends AbstractContext
{
	/**
	 * @return string
	 */
	public function getEnv()
	{
		return 'dev';
	}
	
	/**
	 * @return string
	 */
	public function getHostname()
	{
		return gethostname();
	}
}
