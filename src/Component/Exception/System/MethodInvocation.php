<?php
namespace PHPCrystal\PHPCrystal\Component\Exception\System;

use PHPCrystal\PHPCrystal\Component\Exception\AbstractException;

class MethodInvocation extends AbstractException
{
	private $className;
	private $methodName;

	public function getClassName()
	{
		return $this->className;
	}

	public function setClassName($className)
	{
		$this->className = $className;
		
		return $this;
	}

	public function getMethodName()
	{
		return $this->methodName;
	}

	public function setMethodName($methodName)
	{
		$this->methodName = $methodName;

		return $this;
	}
}
