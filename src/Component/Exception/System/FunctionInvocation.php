<?php
namespace PHPCrystal\PHPCrystal\Component\Exception\System;

use PHPCrystal\PHPCrystal\Component\Exception\AbstractException;

class FunctionInvocation extends AbstractException
{
	public function addFuncName($func)
	{
		return $this;
	}
	
	public function addFuncArgs($argsArray)
	{
		return $this;
	}
}
