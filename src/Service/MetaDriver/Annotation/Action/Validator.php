<?php
namespace PHPCrystal\PHPCrystal\Service\MetaDriver\Annotation\Action;

use PHPCrystal\PHPCrystal\Service\MetaDriver\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *  @Attribute("class", type="string"),
 *  @Attribute("targetMethod", type="string")
 * })
 */
class Validator extends AbstractAnnotation
{
	private $className;
	private $defaultName;
	private $targetHttpMethods = ['POST'];

	/**
	 * @api
	 */
	public function __construct(array $values)
	{
		if (isset($values['class'])) {
			$this->className = $values['class'];
		} else if (isset($values['value'])) {
			$this->defaultName = $values['value'];
		}
		
		if (isset($values['httpMethod'])) {
			$this->targetHttpMethods = explode('|', $values['targetMethod']);
		}
	}
	
	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}
	
	/**
	 * @return string
	 */
	public function getDefaultName()
	{
		return $this->defaultName;
	}
	
	/**
	 * @return array
	 */
	public function getTargetMethods()
	{
		return $this->targetHttpMethods;
	}
}
