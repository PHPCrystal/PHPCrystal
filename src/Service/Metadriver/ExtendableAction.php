<?php
namespace PHPCrystal\PHPCrystal\Service\Metadriver;

use PHPCrystal\PHPCrystal\Annotation\Action as Action;

class ExtendableAction extends AbstractExtendable
{	
	/**
	 * @return 
	 */
	public function getActionAnnotation()
	{
		foreach ($this->getAnnotations() as $annot) {
			if ($annot instanceof Action\Rule) {
				return $annot;
			}
		}
	}

	/**
	 * @return 
	 */
	public function getControllerMethodAnnotation()
	{
		foreach ($this->getAnnotations() as $annot) {
			if ($annot instanceof Action\ControllerMethod) {
				return $annot;
			}
		}
	}
}
