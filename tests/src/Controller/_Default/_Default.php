<?php
namespace PHPCrystal\PHPCrystalTest\Controller\_Default;

use PHPCrystal\PHPCrystal\Component\MVC\Controller\AbstractController;

class _Default extends AbstractController
{
	public function indexAction()
	{
		return 'Unit tests rock!';
	}
	
	/**
	 * @return void
	 */
	public function editUserProfileAction()
	{
		
	}
}
