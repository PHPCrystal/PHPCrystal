<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Context;

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
