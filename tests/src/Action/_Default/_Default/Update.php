<?php
namespace PHPCrystal\PHPCrystalTest\Action\_Default\_Default;

use PHPCrystal\PHPCrystal\Component\MVC\Controller\Action\AbstractAction;

/**
 * @ControllerMethod("editUserProfileAction")
 * @Rule(method="POST", matchPattern="/user/<d:user_id>/profile/edit/")
 */
class Update extends AbstractAction
{
	
}
