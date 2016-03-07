<?php
namespace PHPCrystal\PHPCrystal;

use PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper;

// Start of main config section
$this->openSection('phpcrystal.phpcrystal');

// Default session settings
$this->serviceSection('session');
	$this->set('storage', Facade\Filesystem::create());
	// if set to true does not accept uninitialized session ID
	$this->set('use_strict_mode', true);
	$this->set('use_trans_sid', false);
	$this->set('save_path', sys_get_temp_dir());
	$this->set('name', 'SID');
	$this->set('cookie_lifetime', 0);
	$this->set('cookie_path', '/');
	$this->set('cookie_domain', $this->getHostname());
	$this->set('cookie_httponly', true);
	$this->set('auto_start', true);
	$this->set('gc_probability', 1);
	$this->set('gc_divisor', 100);
	$this->set('gc_maxlifetime', 1800);
$this->closeSection();

$this->serviceSection('cache');
	$this->set('driver', Facade\Memcached::create())
		->addServer('localhost')
		->setOption(\Memcached::OPT_LIBKETAMA_COMPATIBLE, true)
	;
$this->closeSection();

// Doctrine ORM 
$this->serviceSection('doctrine');
	$this->set('proxyDir', FileHelper::create('@cache/doctrine/proxy')); // directory for proxy class files
	$this->set('proxyNamespace', 'Model\\Doctrine\\Proxy\\');
	$this->set('modelNamespace', 'Model\\Doctrine\\');
	$this->set('modelPaths', [FileHelper::create('@app/Model/Physical')]);
	$this->set('entityPaths', [FileHelper::create('@app', 'src', 'Model', 'Entity2')]);
	$this->set('dbal.autocommit', false);
	$this->set('dbal.driver', 'pdo_mysql');
$this->closeSection();

// End of the main config section
$this->closeSection();
