<?php
namespace PHPCrystal\PHPCrystal\Service\Cache\Driver\Memcached;

use PHPCrystal\PHPCrystal\Component\Factory as Factory;
use PHPCrystal\PHPCrystal\Contract as Contract;
use PHPCrystal\PHPCrystal\_Trait\ApplicationAware;

class Memcached implements Contract\Cache, Factory\Aware\ApplicationInterface
{
	use ApplicationAware;
		
	private $servers = array();
	private $handle;
	private $opts;
	private $lastCasToken;
	
	public function __call($name, $args)
	{
		if (is_callable([$this->handle, $name])) {
			return $this->handle->{$name}(...$args);
		}
	}
	
	public function __construct()
	{
		// use persistent connection. class name will serve as connection id
		$this->handle = new \Memcached(__CLASS__);		
	}

	public function init()
	{
		if (0 == count($this->handle->getServerList())) {
			foreach ($this->servers as $server) {
				$this->handle->addServer($server->getHost(), $server->getPort(),
					$server->getWeight());
			}
		}
	}
	
	/**
	 * @return void
	 */
	public function reset()
	{
		$this->servers = [];
		$this->handle->resetServerList();
	}

	/**
	 * @return $this
	 */
	public function addServer($host, $port = 11211, $weight = 0)
	{
		$this->servers[] = new Server($host, $port, $weight);
		
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function get($key, $defaultValue = null)
	{
		$casToken = null;
		$value = $this->handle->get($key, null, $casToken);
		$this->lastCasToken = $casToken;
		
		return $this->handle->getResultCode() == \Memcached::RES_NOTFOUND ?
			$defaultValue : $value;
	}
	
	/**
	 * 
	 */
	public function set($key, $value, $ttl = null)
	{
		$this->handle->set($key, $value, $ttl);
	}
	
	/**
	 * Retrieves and then deletes an item from the cache
	 * 
	 * @return mixed
	 */
	public function pull($key)
	{
		
	}
}
