<?php
namespace PHPCrystal\PHPCrystal\Component\Exception\System;

use PHPCrystal\PHPCrystal\Component\Exception\AbstractException;

class WrongType extends AbstractException
{
	private $expectedType;
	private $receivedType;

	public static function create($expectedType, $receivedType)
	{
		$errMessage = static::format('Received data has wrong type %s, expected %s',
			$receivedType, $expectedType);		
		$excep =  new static($errMessage);
		$excep->expectedType = $expectedType;
		if ( ! is_string($receivedType)) {
			$receivedType = gettype($receivedType);
		}
		$excep->receivedType = $receivedType;
		
		return $excep;
	}
}
