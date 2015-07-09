<?php
namespace PHPCrystal\PHPCrystal\Component\Service;

abstract class AbstractSubcontractor extends AbstractService
{
	private $contractorVendor;
	private $contractorName;
	
	/**
	 * @return boolean
	 */
	final public static function hasLazyInit()
	{
		return true;
	}
	
	/**
	 * @return boolean
	 */
	final public static function isSingleton()
	{
		return false;
	}
	
	/**
	 * @return Contractor
	 */
	final public function getContractorVednor()
	{
		return $this->contractorVendor;
	}
	
	/**
	 * @return void
	 */
	final public function setContractorVendor($name)
	{
		$this->contractorVendor = $name;
	}
	
	/**
	 * @return Contractor
	 */
	final public function getContractorName()
	{
		return $this->contractorName;
	}
	
	/**
	 * @return void
	 */
	final public function setContractorName($name)
	{
		$this->contractorName = $name;
	}
	
	/**
	 * @retun
	 */
	final public function getContractorConfig()
	{
		$context = $this->getApplication()->getContext();
		
		return $context->pluck($this->getContractorVednor() . '.' . $this->getContractorName());
	}
}
