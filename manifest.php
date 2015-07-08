<?php
namespace PHPCrystal\PHPCrystal;

use PHPCrystal\PHPCrystal\Component\Filesystem\PathResolver;
use PHPCrystal\PHPCrystal\Facade as Facade;

$this->set('env', 'prod');

$this->addPathAlias('cache', '@app/cache');
$this->addPathAlias('web', '@app/public_html');
$this->addPathAlias('template', '@app/resources/template');

$this->openSection('app');
	$this->set('hostname', 'locahost');
$this->closeSection();


$this->openSection('phpcrystal.core');

	$this->set('twig.debug', true);
	$this->set('twig.auto_reaload', true);
	$this->set('twig.cache', PathResolver::create('@cache'));
	$this->set('twig.templates', PathResolver::create('@template'));
	$this->set('twig.autoescape', true);

$this->closeSection();

$this->openSection('phpcrystal.core.cache');

	$this->set('driver', Facade\Memcached::create())
		->addServer('localhost')
		->setOption(\Memcached::OPT_LIBKETAMA_COMPATIBLE, true)
	;

$this->closeSection();

// Database common settings.
$this->openSection('phpcrystal.core.database');

	$this->set('driver', 'pdo_mysql');
	$this->set('user', 'root');
	$this->set('password', '');
	$this->set('dbname', '');
	$this->set('charset', 'UTF8');

$this->closeSection();

// Doctrine ORM default setup
$this->openSection('phpcrystal.core.doctrine');

	$this->set('proxyDir', PathResolver::create('@cache/doctrine/proxy')); // directory for proxy class files
	$this->set('proxyNamespace', 'Model\\Doctrine\\Proxy\\');
	$this->set('modelNamespace', 'Model\\Doctrine\\');
	$this->set('modelPaths', [PathResolver::create('@app/Model/Physical')]);
	$this->set('entityPaths', [PathResolver::create('@app/Model/Physical/Entity')]);
	$this->set('dbal.autocommit', false);
	
$this->closeSection();


