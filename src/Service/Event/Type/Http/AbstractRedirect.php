<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\Http;

use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Component\Http\Uri,
	PHPCrystal\PHPCrystal\Component\Http\Response\Header as ResponseHeader
;

abstract class AbstractRedirect extends AbstractResponse
{
	protected $locUri;
	protected $redirectCode;
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getLocationUri()
	{
		return $this->locUri;
	}
	
	/**
	 * @return $this
	 */
	public function setLocationUri(Uri $uri)
	{
		$this->locUri = $uri;
		
		return $this;
	}

	/**
	 * @return void
	 */
	public function output()
	{
		$uri = $this->getLocationUri();
		$this->httpResponse->setStatusCode($this->redirectCode);
		ResponseHeader\Location::create($uri)->save();
		$this->httpResponse->outputHeaders();
	}
}
