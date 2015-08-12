<?php
namespace PHPCrystal\PHPCrystalTest\Annotation\Action;

use PHPCrystal\PHPCrystalTest\TestCase;
use PHPCrystal\PHPCrystal\Annotation\Action\Route;
use Doctrine\Common\Annotations\DocParser;

class RuleTest extends TestCase
{
	private $parser;

	public function setUp() {
		parent::setUp();
		$this->parser = new DocParser();
		$this->parser->addNamespace('PHPCrystal\PHPCrystal\Annotation\Action');
		$this->parser->addNamespace('PHPCrystal\PHPCrystal\Annotation\Common');		
	}

	public function testRouteAnnot1()
	{
		$annots = $this->parser->parse(<<<DocBlock
/**
 * @Route(method="PUT", matchPattern="/payment/{trans_id}/cancel/")
 * @RoutePlaceholder(name="trans_id", isInteger=true)
 */
DocBlock
		, '');

		$routeAnnot = $annots[0];
		$routeAnnot->addPlaceholderAnnots([$annots[1]]);
		$routeAnnot->setURIMatchRegExp($routeAnnot->convertMatchPatternToRegExp(
			$routeAnnot->getMatchPattern()));		
		$this->assertEquals('PUT', $routeAnnot->getAllowedHttpMethods()[0]);
		$this->assertRegExp($routeAnnot->getURIMatchRegExp(), '/payment/23902742/cancel/');
		$this->assertRegExp($routeAnnot->getURIMatchRegExp(), '/payment/9742282/cancel');
	}
	
	public function testRouteAnnot2()
	{
		$annots = $this->parser->parse(<<<DocBlock
/**
 * @Route(method="GET", matchPattern="/account/view/{user_name}")
 * @RoutePlaceholder(name="user_name", regExp="[\w\d]+@[\w\d]+\.com")
 */
DocBlock
		, '');

		$routeAnnot = $annots[0];
		$routeAnnot->addPlaceholderAnnots([$annots[1]]);
		$routeAnnot->setURIMatchRegExp($routeAnnot->convertMatchPatternToRegExp(
			$routeAnnot->getMatchPattern()));		
		$this->assertRegExp($routeAnnot->getURIMatchRegExp(), '/account/view/john@mail.com/');
	}	
}
