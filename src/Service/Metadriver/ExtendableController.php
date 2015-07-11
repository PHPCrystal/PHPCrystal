<?php
namespace PHPCrystal\PHPCrystal\Service\Metadriver;

class ExtendableController extends AbstractExtendable
{
	public function __construct($baseClass, $extendedClass) {
		$a  = 1;
		parent::__construct($baseClass, $extendedClass);
	}
}
