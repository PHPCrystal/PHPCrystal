<?php
namespace PHPCrystal\PHPCrystal\Component\MVC\Controller\Validator;

use PHPCrystal\PHPCrystal\Component\Factory as Factory;

abstract class AbstractValidator
{
	private $input;
	private $rules = array();
	private $errors = array();
	protected $sanitizeFlag = true;

	final public function __construct()
	{
		$this->defineRules();
	}
	
	final public function getInput()
	{
		return $this->input;
	}
	
	final public function setInput($input)
	{
		$this->input = $input;
	}
	
	/**
	 * Return true if input data is valid
	 * 
	 * @return boolean
	 */
	final public function run()
	{
		foreach ($this->getRules() as $itemName => $rule) {
			$value = $this->input->get($itemName);
			
			if ( ! $rule->validate($value)) {
				$this->addError($itemName, $rule->getErrorMessage());				
			}

			if ($this->sanitizeFlag) {
				$newValue = $rule->sanitize($value);
				$this->input->set($itemName, $newValue);
			}			
		}
		
		return count($this->getErrors()) ? false : true;
	}
	
	final public function addRule($itemName, $rule)
	{
		$this->rules[$itemName] = $rule;
	}
	
	final public function addError($itemName, $errorMsg)
	{
		$this->errors[$itemName] = $errorMsg;
	}
	
	final public function getRules()
	{
		return $this->rules;
	}
	
	final public function getErrors()
	{
		return $this->errors;
	}
	
	abstract protected function defineRules();
}