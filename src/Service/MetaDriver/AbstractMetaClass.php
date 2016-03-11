<?php
namespace PHPCrystal\PHPCrystal\Service\MetaDriver;

class AbstractMetaClass
{
	/** @var string */
	private $className;
	
	/** @var array */
	private $annots;
	
	public function __construct($className, array $annots)
	{
		$this->className = $className;
		$this->annots = $annots;
	}
	
	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}
	
	/**
	 * @return array
	 */
	public function getAnnotations()
	{
		return $this->annots;
	}	
}
