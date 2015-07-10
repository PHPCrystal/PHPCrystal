<?php
namespace PHPCrystal\PHPCrystal\Service\Metadriver;

abstract class AbstractMetaContainer
{
	/**
	 * @var array
	 */
	private $annotations = [];
	
	/**
	 * @return array
	 */
	public function getAnnotations()
	{
		return $this->annotations;
	}
	
	/**
	 * @return void
	 */
	public function addAnnotation($annot)
	{
		$this->annotations[] = $annot;
	}
}
