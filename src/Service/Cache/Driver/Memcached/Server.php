<?php
namespace PHPCrystal\PHPCrystal\Service\Cache\Driver\Memcached;

use PHPCrystal\PHPCrystal\Component\Service\AbstractService;
use PHPCrystal\PHPCrystal\Component\Factory as Factory;

class Server extends AbstractService
{
	/**
	 * @var string host or UNIX domain socket filename
	 */
	private $host;
	private $port;
	private $weight;
	
	public function __construct($host, $port, $weight)
	{
		if (0 === strpos($host, 'unix://')) {
			$this->port = 0;
		} else {
			$this->port = $port;
		}
		$this->host = $host;
		$this->weight = $weight;
	}
	
	/**
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}
	
	/**
	 * @return integer
	 */
	public function getPort()
	{
		return $this->port;
	}
	
	/**
	 * @return integer
	 */
	public function getWeight()
	{
		return $this->weight;
	}
}
