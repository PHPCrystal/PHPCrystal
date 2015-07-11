<?php
namespace PHPCrystal\PHPCrystal\Service\Event;

use PHPCrystal\PHPCrystal\Contract as Contract;
use PHPCrystal\PHPCrystal\Component\Factory as Factory;
use PHPCrystal\PHPCrystal\Service\Event as Event;

abstract class AbstractAppListener extends AbstractNode  implements
	Factory\Aware\DependencyInjectionInterface,
	Factory\InitiableInterface
{
	/**
	 * @var \PHPCrystal\PHPCrystal\Contract\Session
	 */
	private $session;
	
	/**
	 * @var \PHPCrystal\PHPCrystal\Contract\Cache
	 */
	private $cache;
	
	/**
	 * @return bool
	 */
	public static function fireEventUponInstantiation()
	{
		return false;
	}

	/**
	 * @api
	 */
	public function __construct(Contract\Cache $cache, Contract\Session $session)
	{
		parent::__construct();
		$this->session = $session;
		$this->cache = $cache;
	}
	
	/**
	 * @return void
	 */
	public function init()
	{
		$this->addEventListener(Event\Type\System\SecurityPolicyApplication::toType(), function($event) {
			$success = $this->onSecurityPolicyApplication($event);
			if ( ! $success) {
				$event->discard();
			}
		});
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Contract\Cache
	 */
	final public function getCache()
	{
		$this->cache->init();
		
		return $this->cache;
	}

	/**
	 * @return \PHPCrystal\PHPCrystal\Contract\Session
	 */
	final public function getSession()
	{
		$this->session->init();
		
		return $this->session;
	}

	//
	// Event hooks
	//
	
	protected function onSecurityPolicyApplication($event)
	{
		if ($event->isAuthRequired() && ! $this->getSession()->isAuthenticated()) {
			return false;
		}
	}
}
