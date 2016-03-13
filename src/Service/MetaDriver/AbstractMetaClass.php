<?php
namespace PHPCrystal\PHPCrystal\Service\MetaDriver;

abstract class AbstractMetaClass
{
	/** @var AbstractPackage */
	private $owner;

	/** @var string */
	protected $targetClass;
	
	/** @var string */
	protected $baseClass;
	
	/** @var array */
	private $annots;
	
	/** @var string */
	private $URI_Str;

	public function __construct($owner, $targetClass, $baseClass, array $annots)
	{
		$this->owner = $owner;
		$this->targetClass = $targetClass;
		$this->baseClass = $baseClass;
		$this->annots = $annots;
	}
	
	public function __sleep()
	{
		return ['targetClass', 'baseClass'];
	}
	
	/**
	 * @return string
	 */
	public function getTargetClass()
	{
		return $this->targetClass;
	}
	
	/**
	 * @return string
	 */
	public function getBaseClass()
	{
		return $this->baseClass;
	}
	
	/**
	 * @return array
	 */
	public function getAnnotations()
	{
		return $this->annots;
	}
}
