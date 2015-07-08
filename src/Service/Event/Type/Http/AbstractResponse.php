<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\Http;

use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Component\Http\Response;

abstract class AbstractResponse extends Event\Type\AbstractInternal implements
	Event\Type\InternalEventInterface
{
	protected $httpResponse;

	public function __construct()
	{
		parent::__construct();
		$this->type = Event\TYPE_UNICAST_SINGLE_DIRECTIONAL_REVERSE;
		$this->httpResponse = new Response;
	}
	
	final public function getHttpResponse()
	{
		return $this->httpResponse;
	}
	
	public function setHttpResponse($object)
	{
		$this->httpResponse = $object;
	}
	
	public function output()
	{
		$this->httpResponse->outputHeaders();
	}
}
