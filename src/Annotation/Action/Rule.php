<?php
namespace PHPCrystal\PHPCrystal\Annotation\Action;

use PHPCrystal\PHPCrystal\Component\Http\Request;
use PHPCrystal\PHPCrystal\Component\Exception\System\LastChineseWarning;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *  @Attribute("method", type="string", required=true),
 *  @Attribute("matchPattern", type="string")
 * })
 */
class Rule
{
	private $allowedHttpMethods = array();
	private $uriMatchRegExp;
	public $matchPattern;
	
	/**
	 * 
	 */
	public function __construct(array $values)
	{
		$this->setAllowedHttpMethods($values['method']);
		if (isset($values['matchPattern'])) {
			$this->matchPattern = $values['matchPattern'];
			$regExp = self::convertMatchPatternToRegexp($values['matchPattern']);
			$this->uriMatchRegExp = $regExp;
		}
	}
	
	/**
	 * @return string
	 */
	public static function convertMatchPatternToRegexp($inputStr)
	{
		$matches = null;
		$patternRegExp = '/<(d|any):([^>]+)>/';		

		while (preg_match($patternRegExp, $inputStr, $matches)) {
			$subpatternName = $matches[2];
			switch ($matches[1]) {
				case 'd':
					$subpatternRegexp = '[0-9]+';
					break;
				
				case 'any':
					$subpatternRegexp = '[^/]+';
			}
			
			$replacement = "(?P<$subpatternName>$subpatternRegexp)";
			$inputStr = preg_replace($patternRegExp, $replacement, $inputStr);
		}

		$inputStr = "|^$inputStr/?$|";

		return $inputStr;
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
					LastChineseWarning::create('Unknown HTTP method "%s"', null, $methodName)
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
}
