<?php
namespace PHPCrystal\PHPCrystalTest;

$this->openSection('phpcrystal.restful-api');
	$this->set('router.hostname', 'api.phpcrystal-framework.com');
$this->closeSection();

$this->serviceSection('phpcrystal.phpcrystal.doctrine');
	$this->set('entityNamespaces', ['MyRepo' => 'PHPCrystal\PHPCrystalTest\Model\Entity']);
$this->closeSection();
