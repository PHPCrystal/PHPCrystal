<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Context;

use PHPCrystal\PHPCrystal\Service\Event\AbstractContext;

class Http extends AbstractContext
{
	private $request;
	
	public function getEnv()
	{
		return 'dev';
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Http\Request
	 */
	final public function getRequest()
	{
		return $this->request;
	}
	
	/**
	 * @return $this
	 */
	final public function setRequest($request)
	{
		$this->request = $request;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getHostname()
	{
		return $this->request->getServerName();
	}
	
	public function getBaseHostname()
	{
		return $this->request->getBaseHostname();
	}
}

