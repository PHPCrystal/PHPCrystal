<?php
namespace PHPCrystal\PHPCrystalTest\Service\Cache;

use PHPCrystal\PHPCrystal\Component\Service\AbstractContractor,
	PHPCrystal\PHPCrystal\Contract as Contract;

class Cache extends AbstractContractor implements
	Contract\Cache
{
	/**
	 * @var object
	 */
	private $driver;
	private $config;
	
	public static function hasLazyInit()
	{
		return true;
	}

	/**
	 * @return void
	 */
	public function init()
	{
		if ($this->isInitialized()) {
			return;
		}
		
		$this->config = $this->getServiceConfig();		
		$this->driver = $this->config->get('driver');
		$this->driver->init();
		$this->isInitialized = true;
	}

	/**
	 * @return \PHPCrystal\PHPCrystal\Contract\Cache
	 */
	public function getDriver()
	{
		return $this->driver;
	}
	
	/**
	 * @return mixed
	 */
	public function get($key, $defaultValue = null)
	{
		return $this->driver->get($key, $defaultValue);
	}
	
	public function set($key, $value, $ttl = null)
	{
		$this->driver->set($key, $value, $ttl);
	}
}