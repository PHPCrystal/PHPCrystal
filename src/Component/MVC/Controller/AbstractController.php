<?php
namespace PHPCrystal\PHPCrystal\Component\MVC\Controller;

use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Component\Factory as Factory;
use PHPCrystal\PHPCrystal\Contract as Contract;

abstract class AbstractController extends Event\AbstractNode implements
	Factory\Aware\DependencyInjectionInterface
{
	private $dbAdapter;
	private $cache;

	public function __construct(Contract\Cache $cache)
	{
		parent::__construct();
		$this->cache = $cache;
	}
	
	/**
	 * @return boolean
	 */
	public static function fireEventUponInstantiation()
	{
		return false;
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Service\Cache\Cache
	 */
	final public function getCache()
	{
		$this->cache->init();
		
		return $this->cache;
	}
	
	public function init()
	{
		$this->addEventListener(Event\Type\Http\Request::toType(), function($event) {
			
		});		
	}
}
