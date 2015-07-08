<?php
namespace PHPCrystal\PHPCrystal\Service\Cache;

use PHPCrystal\PHPCrystal\Component\Service\AbstractContractor;
use PHPCrystal\PHPCrystal\Contract as Contract;

class Cache extends AbstractContractor implements
	Contract\Cache
{
	/**
	 * @var object
	 */
	private $driver;
	
	public static function hasLazyInit()
	{
		return true;
	} 

	/**
	 * @return
	 */
	public function init()
	{
		// call of this method must be idemponent
		if ($this->isInitialized()) {
			return;
		}
		$context = $this->getApplication()->getContext();
		$cacheOpts = $context->pluck('phpcrystal.core.cache');
		$this->driver = $cacheOpts->get('driver');
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