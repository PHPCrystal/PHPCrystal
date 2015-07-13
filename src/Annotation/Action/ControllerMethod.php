<?php
namespace PHPCrystal\PHPCrystal\Annotation\Action;

use PHPCrystal\PHPCrystal\Component\MVC\Controller\Action\AbstractAction;
use PHPCrystal\PHPCrystal\Component\Exception as Exception;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class ControllerMethod
{
	/**
	 * @var string
	 */
	private $methodName;
	
	/**
	 * @api
	 */
	public function __construct(array $values)
	{
		$this->methodName = $values['value'];
	}
	
	/**
	 * @return string
	 */
	public function getMethodName()
	{
		return $this->methodName;
	}
}
