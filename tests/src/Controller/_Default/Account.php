<?php
namespace PHPCrystal\PHPCrystalTest\Controller\_Default;

use PHPCrystal\PHPCrystal\Component\MVC\Controller\AbstractQuartzController;

/**
 * @SecurityPolicy(authRequired=true)
 */
class Account extends AbstractQuartzController
{
	public function editUserProfileAction()
	{
		return __CLASS__;
	}
	
	public function routeDefaultParamAction()
	{
		return __CLASS__;
	}
}
