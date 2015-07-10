<?php
namespace PHPCrystal\PHPCrystal\Service\Metadriver;

use PHPCrystal\PHPCrystal\Facade as Facade;

abstract class AbstractExtendable extends AbstractMetaContainer
{
	protected $baseClass;
	protected $extendedClass;
	
	/**
	 * @var bool
	 */
	protected $isExtended = false;

	/**
	 * @api
	 */
	public function __construct($baseClass, $extendedClass)
	{
		$this->baseClass = $baseClass;
		$this->extendedClass = $extendedClass;
		
		if ( ! empty($extendedClass)) {
			$this->isExtended = true;
		}
		
		$this->addAnnotations(Facade\Metadriver::getClassAnnotations($this->getBaseClass()));
	}
	
	/**
	 * @return string
	 */
	final public function getBaseClass()
	{
		return $this->baseClass;
	}
	
	/**
	 * @return string
	 */
	final public function getExtendedClass()
	{
		return $this->extendedClass;
	}
	
	/**
	 * @return boolean
	 */
	final public function isExtended()
	{
		return $this->isExtended;
	}
	
	/**
	 * @return string
	 */
	final public function getTargetClass()
	{
		return $this->isExtended() ?
			$this->getExtendedClass() : $this->getBaseClass();
	}
}
