<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\Http;

use PHPCrystal\PHPCrystal\Service\Event\Type\AbstractExternal;
use PHPCrystal\PHPCrystal\Service\Event\Context\Http;
use PHPCrystal\PHPCrystal\Component\Http\Request as HttpRequest;
use PHPCrystal\PHPCrystal\Service\Event as Event;

class Request extends AbstractExternal
{
	private $httpRequest;
	
	public function __construct()
	{
		parent::__construct();
		$this->type = Event\TYPE_UNICAST_BIDIRECTIONAL;
		$this->setAutoTriggerEvent(Event\Type\Http\Response200::create());
	}
	
	/**
	 * @return $this
	 */
	public static function create(...$args)
	{
		$event = new static();
		$event->httpRequest = HttpRequest::createFromGlobals();
		
		return $event;
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Http\Request
	 */
	final public function getRequest()
	{
		return $this->httpRequest;
	}
	
	/**
	 * @return void
	 */
	final public function setRequest($request)
	{
		$this->httpRequest = $request;
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Service\Event\Context\Http
	 */
	public function createContext()
	{
		return Http::create('httpContext')
			->setRequest($this->httpRequest);
	}
}
