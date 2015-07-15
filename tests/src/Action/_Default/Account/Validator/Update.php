<?php
namespace PHPCrystal\PHPCrystalTest\Actiion\_Default\_Default\Validator;

use PHPCrystal\PHPCrystal\Component\MVC\Controller\Validator\AbstractValidator;
use PHPCrystal\PHPCrystal\Component\MVC\Controller\Validator\Rule as Rule;

class Update extends AbstractValidator
{
	protected function defineRules()
	{
		$this
			->addRule(Rule\Email::create('email'))
		;
	}
}