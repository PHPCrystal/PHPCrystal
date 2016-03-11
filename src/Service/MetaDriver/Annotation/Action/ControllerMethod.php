<?php
namespace PHPCrystal\PHPCrystal\Service\MetaDriver\Annotation\Action;

use PHPCrystal\PHPCrystal\Service\MetaDriver\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class ControllerMethod extends AbstractAnnotation
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
