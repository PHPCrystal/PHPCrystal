<?php
namespace PHPCrystal\PHPCrystalTest\Component\PhpParser;

use PHPCrystal\PHPCrystalTest\TestCase;
use PHPCrystal\PHPCrystal\Component\PhpParser\PhpParser;

class PhpParserTest extends TestCase
{
	public function testParseNamespace()
	{
		$namespace = PhpParser::loadFromFile(__FILE__)
			->parseNamespace();
		$this->assertEquals(__NAMESPACE__, $namespace);
	}
	
	public function testParseClass()
	{
		$fqcn = PhpParser::loadFromFile(__FILE__)
			->parseClass();
		$this->assertEquals(__CLASS__, $fqcn);
	}
	
	public function testParseInterface()
	{
		$interface = PhpParser::loadFromString(<<<PHP
namespace MyApp;

interface Config {
	public function getVar();
}
PHP
		)->parseInterface();
		$this->assertEquals('MyApp\\Config', $interface);
	}
}
