<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\Http;

class Response200 extends AbstractResponse
{
	public function __construct()
	{
		parent::__construct();
		$this->httpResponse->setStatusCode(200);
	}

	/**
	 * @return string
	 */
	public function output()
	{
		parent::output();

		echo $this->getResult();
	}
}
