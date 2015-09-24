<?php
namespace PHPCrystal\PHPCrystal\Component\Http\Response\Header;

use PHPCrystal\PHPCrystal\Component\Http\Uri,
	PHPCrystal\PHPCrystal\Component\Exception\System\FrameworkRuntimeError
;

class Location extends AbstractField
{
	private $uri;

	public function __construct($uri)
	{
		parent::__construct();
		$this->uri = Uri::create($uri);

		if ( ! $this->uri->isAbsolute()) {
			FrameworkRuntimeError::create('Redirect URI must be absolute, "%s" is given', null,
				$this->uri->toString())
				->_throw()
			;
		}
	}
	
	/**
	 * @return void
	 */
	public function output()
	{
		$uriString = $this->uri->toString();
		
		header("Location: {$uriString}");
	}
}
