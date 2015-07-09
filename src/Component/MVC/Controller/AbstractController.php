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
	private $session;

	/**
	 * @api
	 */
	final public function __construct(Contract\Cache $cache, Contract\Session $session)
	{
		parent::__construct();
		$this->cache = $cache;
		$this->session = $session;
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
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Service\Session\Session
	 */
	final public function getSession()
	{
		$this->session->init();
		
		return $this->session;
	}
}
