<?php
namespace PHPCrystal\PHPCrystal\Component\PhpParser;

use Doctrine\Common\Annotations\TokenParser;

class PhpParser extends TokenParser
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
		$name = $this->parseNamespace();
		$startClassDecl = false;
		
		while (($token = $this->next())) {
			if ($token[0] === T_FINAL || $token[0] === T_ABSTRACT || $token[0] === T_CLASS) {
				$startClassDecl = true;
			} else if ($startClassDecl && $token[0] === T_STRING) {
				$name .= '\\' . $token[1];
				break;
			}
		}
		
		return $name;
	}
	
	/**
	 * @return string
	 */
	public function parseInterface()
	{
		$name = $this->parseNamespace();
		
		while (($token = $this->next())) {
			if ($token[0] === T_INTERFACE)  {
				$name .= '\\' . $this->next()[1];
				break;
			}
		}
		
		return $name;
	}
}
