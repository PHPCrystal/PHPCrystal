<?php
namespace PHPCrystal\PHPCrystal\Service\MetaDriver\MetaClass;

use PHPCrystal\PHPCrystal\Service\MetaDriver\AbstractMetaClass,
	PHPCrystal\PHPCrystal\Service\MetaDriver\Annotation as Annotation;

class Extension extends AbstractMetaClass
{
	private $dirname;
	
	/**
	 * @api
	 */
	public function __construct($dirname)
	{
		parent::__construct($className, $annots);
	}

	/**
	 * @return string
	 */
	public function getDirectoryName()
	{
		return $this->dirname;
	}
	
	/**
	 * @return void
	 */
	public function setDirectoryName($dirname)
	{
		$this->dirname = $dirname;
	}
}
