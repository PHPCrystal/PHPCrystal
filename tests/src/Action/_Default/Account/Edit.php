<?php
namespace PHPCrystal\PHPCrystalTest\Action\_Default\Account;

use PHPCrystal\PHPCrystal\Component\MVC\Controller\Action\AbstractAction;

/**
 * @ControllerMethod("editUserProfileAction")
 * @Rule(method="POST", matchPattern="/user/<d:user_id>/edit/")
 * @Validator("Update")
 */
class Edit extends AbstractAction
{
	protected function onDataValidationFail($event, $validator)
	{
		throw new \RuntimeException('Data validation failed');
	}
}
