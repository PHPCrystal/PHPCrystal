<?php
namespace PHPCrystal\PHPCrystal\Component\MVC\Controller\Validator\Rule;

use PHPCrystal\PHPCrystal\Component\MVC\Controller\Validator as Validator;

class Email extends Validator\AbstractRule
{
	public function validate($value)
	{
		$result = filter_var($value, FILTER_VALIDATE_EMAIL);
		
		return $result === false ? false : true;
	}
}
