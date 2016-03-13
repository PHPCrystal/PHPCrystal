<?php
namespace PHPCrystal\PHPCrystal\Service\MetaDriver\MetaClass;

use PHPCrystal\PHPCrystal\Service\MetaDriver\AbstractMetaClass,
	PHPCrystal\PHPCrystal\Service\MetaDriver\Annotation as Annotation;

class Action extends AbstractMetaClass
{
	/** @var string */
	private $match_URI_regExp;
	
	public function __construct($targetClass, array $annots, $baseClass)
	{
		parent::__construct($targetClass, $annots, $baseClass);
		$this->handleAnnots($annots);
	}
	
	protected function handleAnnots($annots)
	{
		foreach ($annots as $annot) {
			if ($annot instanceof Annotation\Action\Route) {
				
			}
		}
	}
}
