<?php
namespace PHPCrystal\PHPCrystal\Component\Http;

class Response
{
	private static $allowedHttpHeaders = [
		'Accept-Ranges ', 'Location', 'Age', 'ETag', 'Proxy-Authenticate',
		'Retry-After', 'Server', 'Vary', 'WWW-Authenticate'];	
	private $httpHeaders = array();
	private $statusCode;
	
	public function addHeader($fieldName, $fieldValue)
	{
		if ( ! in_array($fieldName, self::$allowedHttpHeaders)) {
			throw new \RuntimeException(sprintf('The HTTP response-header field "%s" is not allowed',
				$fieldName));
		}
		$this->httpHeaders[$fieldName] = $fieldValue;
	}
	
	final public function setStatusCode($code)
	{
		$this->statusCode = $code;
	}
	
	final public function getStatusCode()
	{
		return $this->statusCode;
	}
	
	public function outputHeaders()
	{
		if (isset($this->statusCode)) {
			http_response_code($this->statusCode);			
		}
		
		foreach ($this->httpHeaders as $fieldName => $fieldValue) {
			header($fieldName . ': ' . $fieldValue);
		}		
	}
}
