<?php
namespace PHPCrystal\PHPCrystalTest\Action\_Default\Account;

use PHPCrystal\PHPCrystal\Component\MVC\Controller\Action\AbstractAction;

/**
 * @ControllerMethod("editUserProfileAction")
 * @Route(method="POST", matchPattern="/account/{user_id}/edit/")
 * @SecurityPolicy(authRequired=true)
 * @Validator("Update")
 */
class Edit extends AbstractAction
{
	protected function onDataValidationFail($event, $validator)
	{
		throw new \RuntimeException('Data validation failed');
	}
}
