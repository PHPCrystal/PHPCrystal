<?php
namespace PHPCrystal\PHPCrystal\Service\DataProvider;

use PHPCrystal\PHPCrystal\Export as Export;
use PHPCrystal\PHPCrystal\Component\Service\AbstractService;
use PHPCrystal\PHPCrystal\Component\Factory as Factory;

abstract class AbstractDataProvider extends AbstractService
{
	private $cache;
	private $orm;
	
	/**
	 * By default each data provider has only one instance 
	 * 
	 * @return true
	 */
	public static function isSingleton()
	{
		return true;
	}

	/**
	 * 
	 */
	final public function __construct(Export\Cache $cache)
	{
		$this->cache = $cache;
	}
	
	/**
	 * 
	 */
	final public function getCache()
	{
		$this->cache->init();
		
		return $this->cache;
	}
}
