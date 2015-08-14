<?php
namespace PHPCrystal\PHPCrystalTest\Controller\_Default;

use PHPCrystal\PHPCrystal\Component\MVC\Controller\AbstractController;

/**
 * @SecurityPolicy(authRequired=true)
 */
class Account extends AbstractController
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
