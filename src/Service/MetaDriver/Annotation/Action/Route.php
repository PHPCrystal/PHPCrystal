<?php
namespace PHPCrystal\PHPCrystal\Service\MetaDriver\Annotation\Action;

use PHPCrystal\PHPCrystal\Service\MetaDriver\Annotation\AbstractAnnotation;


use PHPCrystal\PHPCrystal\Component\Http\Request;
use PHPCrystal\PHPCrystal\Component\Exception\System\FrameworkRuntimeError;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *  @Attribute("method", type="string", required=true),
 *  @Attribute("matchPattern", type="string", required=true),
 * })
 */
class Route extends AbstractAnnotation
{
	private $allowedHttpMethods = array();
	private $matchPattern;
	private $placeholderAnnots = [];
	private $uriMatchRegExp;

	private $defaultItemValue;
	private $defaultItemKey;

	/**
	 * 
	 */
	public function __construct(array $values)
	{
		$this->setAllowedHttpMethods($values['method']);
		$this->matchPattern = $values['matchPattern'];
	}
	
	/**
	 * @return void
	 */
	public function addPlaceholderAnnots(array $annots)
	{
		$this->placeholderAnnots = array_merge($this->placeholderAnnots, $annots);
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Annotation\Action\RoutePlaceholder|null
	 */
	public function getPlaceholderAnnotByName($placeholderName)
	{
		foreach ($this->placeholderAnnots as $annot) {
			if ($annot->getName() == $placeholderName) {
				return $annot;
			}
		}

		return null;
	}
	
	/**
	 * @return null
	 */
	private function singlePlaceholderCheck($matchPattern)
	{
		if (preg_match("/\{{2,}/", $matchPattern,  $matches)) {
			FrameworkRuntimeError::create('Only one placeholder is allowed for the pattern "%s"', null, $matchPattern)
				->_throw();
		}
	}

	/**
	 * @return string
	 */
	private function replacePlaceholder($placeholderName, &$subject)
	{
		$replacementRegExp = "(?<{$placeholderName}>";
		$matches = null;

		if (preg_match("/\{{$placeholderName}\}(.{1,1})/", $subject, $matches)) {
			$lookAhead = $matches[1];

			if ($lookAhead[0] == '{') {
				FrameworkRuntimeError::create('Common placeholders cannot be adjacent, pattern %s', null, $subject)
					->_throw();
			}

			$stopChar = $lookAhead[0];
			$replacementRegExp .= "[^{$stopChar}]+)";
		} else {
			$replacementRegExp .= '[^/]+)'; // match everything until the end of the string
		}

		$subject = preg_replace("|{{$placeholderName}}|", $replacementRegExp, $subject, 1);
	}

	/**
	 * @return string
	 */
	private function replacePlaceholderWithRegExp($placeholderName, $regExp, &$subject)
	{
		$subject = preg_replace("|{{$placeholderName}}|", "(?<$placeholderName>$regExp)", $subject, 1);
	}

	/**
	 * @return string
	 */
	public function convertMatchPatternToRegExp($matchPattern)
	{
		$matches = null;
		
		if (preg_match_all('|{([^}]+)}|', $matchPattern, $matches)) {
			$matchRegExp = $matchPattern;

			while (($phName = array_shift($matches[1]))) {
				$phAnnot = $this->getPlaceholderAnnotByName($phName);

				if ( ! $phAnnot) {
					$this->replacePlaceholder($phName, $matchRegExp, '/');
					continue;
				}

				if ($phAnnot->isInteger()) {
					$intRegExp = "[0-9]+";
					$this->replacePlaceholderWithRegExp($phName, $intRegExp,
						$matchRegExp);
					continue;
				}

				// route default param
				if ($phAnnot->hasDefaultValue()) {
					$this->singlePlaceholderCheck($matchPattern);
					$stopChars = $phAnnot->getMatchUntilCharSet();
					$defaultRegExp = "([^{$stopChars}]+)?";
					$this->replacePlaceholderWithRegExp($phName, $defaultRegExp,
						$matchRegExp);
					$this->defaultItemValue = $phAnnot->getDefaultValue();
					$this->defaultItemKey = $phName;
					break;
				}

				if ( ! empty($phAnnot->getRegExp())) {
					$this->replacePlaceholderWithRegExp($phName, $phAnnot->getRegExp(),
						$matchRegExp);
					continue;
				}
			}
		} else {
			$matchRegExp = $matchPattern;
		}

		return '|^' . rtrim($matchRegExp, '/') . '/?$|'; 
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
	public function setAllowedHttpMethods($mixed)
	{
		if ('ALL' == $mixed) {
			return;
		} else if (is_string($mixed)) {
			$parts = explode('|', $mixed);
			foreach ($parts as $methodName) {
				if ( ! in_array($methodName, Request::getKnownHttpMethods())) {
					FrameworkRuntimeError::create('Unknown HTTP method "%s"', null, $methodName)
						->_throw();
				}
				$this->allowedHttpMethods[] = $methodName;
			}
		} else {
			$this->allowedHttpMethods = $mixed;
		}
	}

	/**
	 * @return string
	 */
	public function getURIMatchRegExp()
	{
		return $this->uriMatchRegExp;
	}
	
	/**
	 * @return void
	 */
	public function setURIMatchRegExp($regExp)
	{
		$this->uriMatchRegExp = $regExp;
	}
	
	/**
	 * @return string
	 */
	public function getMatchPattern()
	{
		return $this->matchPattern;
	}
	
	/**
	 * @return bool
	 */
	public function hasDefaultItemValue()
	{
		return empty($this->defaultItemValue) ? false : true;
	}
	
	/**
	 * @return string
	 */
	public function getDefaultItemKey()
	{
		return $this->defaultItemKey;
	}
	
	/**
	 * @return string
	 */
	public function getDefaultItemValue()
	{
		return $this->defaultItemValue;
	}
}
