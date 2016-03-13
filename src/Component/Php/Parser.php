<?php
namespace PHPCrystal\PHPCrystal\Component\Php;

use Doctrine\Common\Annotations\TokenParser;

class Parser extends TokenParser
{
	/**
	 * @return $this
	 */
	public static function loadFromFile($filename)
	{
		$content = file_get_contents($filename);
		
		return new static($content);
	}
	
	/**
	 * @return $this
	 */
	public static function loadFromString($phpSource)
	{
		return new static('<?php ' . $phpSource);
	}
	
	/**
	 * @return string
	 */
	public function parseNamespace()
	{
		$nsDeclStart = false;
		$name = '';

		while (($token = $this->next())) {
			if ($token[0] === T_NAMESPACE) {
				$nsDeclStart = true;
			} else  if ($nsDeclStart) {
				if ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR) {
					$name .= $token[1];
				} else {
					break;
				}
			}
		}
		
		return $name;
	}
	
	/**
	 * @return string
	 */
	public function parseClass()
	{
		$class_NS = $this->parseNamespace();

		while (($token = $this->next())) {
			if ($token[0] === T_CLASS) {
				return $class_NS . '\\' . $this->next()[1];
			}
		}
		
		return null;
	}
	
	/**
	 * @return string
	 */
	public function parseInterface()
	{
		$interafce_NS = $this->parseNamespace();
		
		while (($token = $this->next())) {
			if ($token[0] === T_INTERFACE)  {
				return $interafce_NS . '\\' . $this->next()[1];
			}
		}

		return null;
	}
}
