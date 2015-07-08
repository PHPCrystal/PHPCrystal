<?php
namespace PHPCrystal\PHPCrystal\Component\Exception\System;

use PHPCrystal\PHPCrystal\Component\Exception\AbstractException;

class Legacy extends AbstractException
{
	private $legacyException;

	/**
	 * @return $this
	 */
	public function setLegacyException($excep)
	{
		$this->legacyException = $excep;
		
		return $this;
	}
	
	/**
	 * @return \Exception
	 */
	public function getLegacyException()
	{
		return $this->legacyException;
	}
}
