<?php
namespace PHPCrystal\PHPCrystal\Service\Metadriver;

use PHPCrystal\PHPCrystal\Annotation\Action as Action;

class ExtendableAction extends AbstractExtendable
{	
	private $controllerMethod;
	private $allowedHttpMethods = array();
	private $uriMatchRegExp;
	
	/**
	 * @return array
	 */
	public function __sleep()
	{
		return array_merge(parent::__sleep(), ['controllerMethod', 'allowedHttpMethods', 'uriMatchRegExp']);
	}

	public function __construct($baseClass, $extendedClass)
	{
		parent::__construct($baseClass, $extendedClass, 'PHPCrystal\PHPCrystal\Annotation\Action');
		
		$refClass = new \ReflectionClass($this->getBaseClass());
		$annots = $this->annotReader->getClassAnnotations($refClass);

		foreach ($annots as $annot) {
			if ($annot instanceof Action\ControllerMethod) {
				$this->setControllerMethod($annot->value);
			} else if ($annot instanceof Action\Rule) {
				$this->setAllowedHttpMethods($annot->getAllowedHttpMethods());
				$this->setUriMatchRegExp($annot->getUriMatchRegExp());
			}
		}
	}

	/**
	 * @return string
	 */
	public function getControllerMethod()
	{
		return $this->controllerMethod;
	}
	
	/**
	 * @return void
	 */
	public function setControllerMethod($name)
	{
		$this->controllerMethod = $name;
	}
	
	/**
	 * @return array
	 */
	public function getAllowedHttpMethods()
	{
		return $this->allowedHttpMethods;
	}
	
	/**
	 * @return void
	 */
	public function setAllowedHttpMethods($methodsArray)
	{
		$this->allowedHttpMethods = $methodsArray;
	}
	
	/**
	 * @return string
	 */
	public function getUriMatchRegExp()
	{
		return $this->uriMatchRegExp;
	}
	
	/**
	 * @return void
	 */
	public function setUriMatchRegExp($regExp)
	{
		$this->uriMatchRegExp = $regExp;
	}
}
