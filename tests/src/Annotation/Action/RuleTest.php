<?php
namespace PHPCrystal\PHPCrystalTest\Annotation\Action;

use PHPCrystal\PHPCrystalTest\TestCase;
use PHPCrystal\PHPCrystal\Annotation\Action\Route;

class RuleTest extends TestCase
{
	public function testConvertMatchPattern()
	{
		$regExp = Route::convertMatchPatternToRegexp('/user/{user_id}/profile/edit');
		$this->assertRegExp($regExp, '/user/4927106/profile/edit');
		
		$regExp = Route::convertMatchPatternToRegexp('/user/{nickname}/profile/edit');
		$this->assertRegExp($regExp, '/user/@myself/profile/edit');		
	}
}
