<?php
namespace PHPCrystal\PHPCrystal\Component\Http;

use PHPCrystal\PHPCrystal\Component\Http\Response\Header as ResponseHeader;

class Response
{
	private $statusCode;
	
	/**
	 * @return integer
	 */
	public function getStatusCode()
	{
		return $this->statusCode;
	}

	public function setStatusCode($code)
	{
		$this->statusCode = $code;
	}

	/**
	 * @return void
	 */
	public function outputHeaders()
	{
		if (isset($this->statusCode)) {
			http_response_code($this->statusCode);			
		}

		// Output response header fields
		foreach (ResponseHeader\AbstractField::getAll() as $field) {
			$field->output();
		}
	}
}
