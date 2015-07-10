<?php
namespace PHPCrystal\PHPCrystal\Service\Metadriver;

use PHPCrystal\PHPCrystal\Annotation\Action as Action;
use PHPCrystal\PHPCrystal\Facade as Facade;

class ExtendableAction extends AbstractExtendable
{	
	private $controllerMethod;
	private $allowedHttpMethods = array();
	private $uriMatchRegExp;
	private $uriMatchPattern;
	
	/**
	 * 
	 */
	public function __construct($baseClass, $extendedClass)
	{
		parent::__construct($baseClass, $extendedClass);

		$annots = Facade\Metadriver::getClassAnnotations($this->getBaseClass());

		foreach ($annots as $annot) {
			if ($annot instanceof Action\ControllerMethod) {
				$this->setControllerMethod($annot->value);
			} else if ($annot instanceof Action\Rule) {
				$this->setAllowedHttpMethods($annot->getAllowedHttpMethods());
				$this->setUriMatchRegExp($annot->getUriMatchRegExp());
				$this->setURIMatchPattern($annot->matchPattern);
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
	
	/**
	 * @return string
	 */
	public function getURIMatchPattern()
	{
		return $this->uriMatchPattern;
	}
	
	/**
	 * @return void
	 */
	public function setURIMatchPattern($pattern)
	{
		$this->uriMatchPattern = $pattern;
	}
}
