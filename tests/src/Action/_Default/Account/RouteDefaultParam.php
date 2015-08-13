<?php
namespace PHPCrystal\PHPCrystalTest\Action\_Default\Account;

use PHPCrystal\PHPCrystal\Component\MVC\Controller\Action\AbstractAction;

/**
 * @ControllerMethod("routeDefaultParamAction")
 * @Route(method="GET", matchPattern="/account/{default_param}")
 * @RoutePlaceholder(name="default_param", defaultValue="master")
 */
class RouteDefaultParam extends AbstractAction
{

}
