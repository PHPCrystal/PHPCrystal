<?php
namespace PHPCrystal\PHPCrystal\Service\Metadriver;

use PHPCrystal\PHPCrystal\Annotation\Action as Action;

class ExtendableAction extends AbstractExtendable
{	
	/**
	 * @return \PHPCrystal\PHPCrystal\Annotation\Action\Rule
	 */
	public function getRuleAnnotation()
	{
		foreach ($this->getAnnotations() as $annot) {
			if ($annot instanceof Action\Rule) {
				return $annot;
			}
		}
	}

	/**
	 * @return \PHPCrystal\PHPCrystal\Annotation\Action\ControllerMethod
	 */
	public function getControllerMethodAnnotation()
	{
		foreach ($this->getAnnotations() as $annot) {
			if ($annot instanceof Action\ControllerMethod) {
				return $annot;
			}
		}
	}
	
	public function getValidatorAnnot()
	{
		foreach ($this->getAnnotations() as $annot) {
			if ($annot instanceof Action\Validator) {
				return $annot;
			}
		}
	}
	
	public function getInputAnnot()
	{
		foreach ($this->getAnnotations() as $annot) {
			if ($annot instanceof Action\Input) {
				return $annot;
			}
		}
	}
}
