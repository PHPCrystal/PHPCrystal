<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\Http;

use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Component\Http\Uri;

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

	public function output()
	{
		$uri = $this->getLocationUri();
		if ( ! $uri->isAbsolute()) {
			throw new \RuntimeException(sprintf('Redirect URI "%s" must be absolute',
				$uri->toString()));
		}
		$this->httpResponse->setStatusCode($this->redirectCode);
		$this->httpResponse->addHeader('Location', $uri->toString());		
		$this->httpResponse->outputHeaders();
	}
}
