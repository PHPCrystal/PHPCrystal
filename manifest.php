<?php
namespace PHPCrystal\PHPCrystal;

use PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper;
use PHPCrystal\PHPCrystal\Facade as Facade;

$this->set('env', 'prod');

$this->openSection('app');
	$this->set('hostname', 'locahost');
$this->closeSection();


$this->openSection('phpcrystal.core');

	$this->set('twig.debug', true);
	$this->set('twig.auto_reaload', true);
	$this->set('twig.cache', FileHelper::create('@cache'));
	$this->set('twig.templates', FileHelper::create('@template'));
	$this->set('twig.autoescape', true);

$this->closeSection();



// Database common settings.
$this->openSection('phpcrystal.phpcrystal.database');

	$this->set('driver', 'pdo_mysql');
	$this->set('user', 'root');
	$this->set('password', '');
	$this->set('dbname', null);
	$this->set('charset', 'UTF8');

$this->closeSection();

$this->serviceSection('phpcrystal.security_guard');
	$this->set('enabled', true);	
	$this->set('csrf-token-cookie-name', 'csrf-token');
	$this->set('csrf-token-header-field-name', 'X-Csrf-Token');
	$this->set('csrf-token-secret-key', mt_rand(0, mt_getrandmax()));
$this->closeSection();
