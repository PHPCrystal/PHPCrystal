<?php
namespace PHPCrystal\PHPCrystal;

require_once __DIR__ . '/src/Annotation/Action/ControllerMethod.php';
require_once __DIR__ . '/src/Annotation/Action/Route.php';
require_once __DIR__ . '/src/Annotation/Action/RoutePlaceholder.php';
require_once __DIR__ . '/src/Annotation/Action/Validator.php';
require_once __DIR__ . '/src/Annotation/Action/Input.php';
require_once __DIR__ . '/src/Annotation/Common/SecurityPolicy.php';

return PHPCrystal::create()
	->setDirectory(__DIR__)
	->setPriority(1)
	->setBuilder('\\PHPCrystal\\PHPCrystal\\Service\\PackageBuilder\\PackageBuilder')
	->setComposerName('phpcrystal/phpcrystal')
	->setDisableAutoloadFlag(true)
	->init()
;
