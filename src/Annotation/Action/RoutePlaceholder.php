<?php
namespace PHPCrystal\PHPCrystal\Annotation\Action;

use PHPCrystal\PHPCrystal\Component\Exception\System\FrameworkRuntimeError;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *  @Attribute("name", type="string", required=true),
 *  @Attribute("defaultValue", type="string"),
 *  @Attribute("isInteger", type="bool"),
 *  @Attribute("matchUntilCharSet", type="string"),
 *  @Attribute("regExp", type="string")
 * })
 */
class RoutePlaceholder
{
	private $name;
	private $defaultValue;
	private $isInteger;
	private $matchUntilCharSet = [];
	private $regExp;

	/**
	 * 
	 */
	public function __construct(array $values)
	{
		$this->name = $values['name'];

		$charset = @$values['matchUntilCharset'];
		for ($i = 0; $i < strlen($charset); $i++) {
			$this->matchUntilCharSet[] = $charset[$i];
		}

		$this->defaultValue = @$values['defaultValue'];
		$this->isInteger = (bool) @$values['isInteger'];
		$this->regExp = @$values['regExp'];

		if ( ! empty($this->regExp) && ! (empty($this->isInteger))) {
			FrameworkRuntimeError::create('RoutePlaceholder annotation attributes `regExp` and (isInteger) are mutually exclusive')
				->_throw();
		}
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getDefaultValue()
	{
		return $this->defaultValue;
	}

	/**
	 * @return bool
	 */
	public function isInteger()
	{
		return $this->isInteger;
	}

	/**
	 * @return string
	 */
	public function getMatchUntilCharSet()
	{
		$charsetArray = empty($this->matchUntilCharSet) ? ['/'] : $this->matchUntilCharSet;
		$charset = '';

		foreach ($charsetArray as $char) {
			$charset .= preg_quote($char, '|');
		}

		return $charset;
	}

	/**
	 * @return string
	 */
	public function getRegExp()
	{
		return $this->regExp;
	}
}
