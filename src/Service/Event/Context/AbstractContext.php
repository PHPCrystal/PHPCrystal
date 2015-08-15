<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Context;

use PHPCrystal\PHPCrystal\Component\Package\Option\Container;

abstract class AbstractContext extends Container implements
	ContextInterface
{
	/**
	 * @return $this
	 */
	public function setEnv($env)
	{
		$this->set('env', $env);
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getEnv()
	{
		return $this->get('env', 'dev');
	}
}
