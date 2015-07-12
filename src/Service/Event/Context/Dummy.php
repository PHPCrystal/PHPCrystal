<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Context;

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
