<?php
namespace PHPCrystal\PHPCrystal\Service\Auth;

use PHPCrystal\PHPCrystal\Component\Service\AbstractContractor;
use PHPCrystal\PHPCrystal\Contract as Contract;

class Auth extends AbstractContractor implements
	Contract\Auth
{
	public function isAuthenticated()
	{
		return false;
	}
}
