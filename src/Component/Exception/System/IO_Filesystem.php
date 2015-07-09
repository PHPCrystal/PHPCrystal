<?php
namespace PHPCrystal\PHPCrystal\Component\Exception\System;

class IO_Filesystem extends AbstractSystem
{
	public static function assertFd($fd, $filename)
	{
		if ( ! is_resource($fd)) {
			static::create('Cannot open file "%s"', null, $filename)
				->addParam($filename)
				->_throw();
		}
	}
}
