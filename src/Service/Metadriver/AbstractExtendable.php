<?php
namespace PHPCrystal\PHPCrystal\Service\Metadriver;

use Doctrine\Common\Annotations\SimpleAnnotationReader;

abstract class AbstractExtendable
{
	protected $baseClass;
	protected $extendedClass;
	protected $isExtended = false;
	protected $annotReader;
	
	/**
	 * @return array
	 */
	public function __sleep()
	{
		return array('baseClass', 'extendedClass', 'isExtended');
	}

	/**
	 * @api
	 */
	public function __construct($baseClass, $extendedClass, $annotNamespace = null)
	{
		$this->baseClass = $baseClass;
		$this->extendedClass = $extendedClass;
		
		if ( ! empty($extendedClass)) {
			$this->isExtended = true;
		}
		
		$this->annotReader = new SimpleAnnotationReader();
		$this->annotReader->addNamespace($annotNamespace);
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
