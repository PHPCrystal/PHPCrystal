<?php
namespace PHPCrystal\PHPCrystal\Component\Http;

use PHPCrystal\PHPCrystal\_Trait\CreateObject;

class Uri extends \Zend\Uri\Uri
{
	use CreateObject;

	public function matchUriPath($regExp, &$matches = null)
	{
		$result = preg_match($regExp, $this->getPath(), $matches);
		
		return $result;
	}
	
	/**
	 * @return this
	 */
	final public function makeBaseUri()
	{
		return new static($this->getScheme() . '://' . $this->getHost());
	}
}

