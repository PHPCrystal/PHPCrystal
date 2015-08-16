<?php
namespace PHPCrystal\PHPCrystal\Service\Event;

use PHPCrystal\PHPCrystal\Contract as Contract;
use PHPCrystal\PHPCrystal\Component\Factory as Factory;
use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Facade\Metadriver;

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
	 * @var array
	 */
	private $annots;
	
	/** @var */
	private $extandableInstance;
	
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
			if ( false === $success) {
				$event->discard();
			}
		});
	}
	
	/**
	 * @return array
	 */
	final public function getAnnotations()
	{
		return $this->annots;
	}
	
	/**
	 * @return void
	 */
	final public function setAnnotations(array $annots)
	{
		$this->annots = $annots;
	}
	
	/**
	 * @return
	 */
	public function getExtendableInstance()
	{
		return $this->extandableInstance;
	}
	
	/**
	 * @return void
	 */
	public function setExtendableInstance($instance)
	{
		$this->extandableInstance = $instance;
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
			$this->onAuthenticationFail($event);
			return false;
		}
	}
	
	/**
	 * @return void
	 */
	protected function onAuthenticationFail($event)
	{
		$event->setAutoTriggerEvent(Event\Type\Http\Response500::create());		
	}
}
