<?php
namespace PHPCrystal\PHPCrystal\Component\Service;

use PHPCrystal\PHPCrystal\Component\Exception\System\FrameworkRuntimeError;

abstract class AbstractSubcontractor extends AbstractService
{
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
	 * @return
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
		if ( ! $this->validateServiceName($name)) {
			FrameworkRuntimeError::create('Contractor name must be a fully qualified service name, "%s" is given"', null, $name)
				->_throw();
		}

		$this->contractorName = $name;
	}
	
	/**
	 * @retun
	 */
	final public function getContractorConfig()
	{
		$context = $this->getMergedConfig();
		
		return $context->pluck($this->getContractorName());
	}
}
