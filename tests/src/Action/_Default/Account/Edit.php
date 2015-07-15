<?php
namespace PHPCrystal\PHPCrystalTest\Action\_Default\_Default;

use PHPCrystal\PHPCrystal\Component\MVC\Controller\Action\AbstractAction;

/**
 * @ControllerMethod("editUserProfileAction")
 * @Rule(method="POST", matchPattern="/user/<d:user_id>/edit/")
 * @Input(URI|POST|GET|COOKIE)
 * @Validator(Update)
 */
class Update extends AbstractAction
{
	
}
