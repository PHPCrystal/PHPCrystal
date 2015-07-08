<?php
namespace PHPCrystal\PHPCrystal\Component\Package;

abstract class AbstractExtension extends AbstractPackage 
{
	/**
	 * @var bool
	 */
	private $disabledFlag;
	
	/**
	 * @return bool
	 */
	public function getDisabledFlag()
	{
		return $this->disabledFlag;
	}
	
	/**
	 * @return $this
	 */
	final public function setDisabledFlag($flagValue)
	{
		$this->disabledFlag = $flagValue;
		
		return $this;
	}
}
