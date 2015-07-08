<?php
namespace PHPCrystal\PHPCrystalTest\Annotation\Action;

use PHPCrystal\PHPCrystalTest\TestCase;
use PHPCrystal\PHPCrystal\Annotation\Action\Rule;

class RuleTest extends TestCase
{
	public function testConvertMatchPattern()
	{
		$regExp = Rule::convertMatchPatternToRegexp('/user/<d:user_id>/profile/edit');
		$this->assertRegExp($regExp, '/user/4927106/profile/edit');
		
		$regExp = Rule::convertMatchPatternToRegexp('/user/<any:user_nickname>/profile/edit');
		$this->assertRegExp($regExp, '/user/@myself/profile/edit');		
	}
}
