<?php
namespace PHPCrystal\PHPCrystal;

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/src/Annotation/Action/ControllerMethod.php';
require_once __DIR__ . '/src/Annotation/Action/Rule.php';
require_once __DIR__ . '/src/Annotation/Common/SecurityPolicy.php';

return Extension::create()
	->setDirectory(__DIR__)
	->setPriority(1)
;
