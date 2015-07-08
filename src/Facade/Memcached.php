<?php
namespace PHPCrystal\PHPCrystal\Facade;

use PHPCrystal\PHPCrystal\Component\Facade\AbstractClassFacade;

class Memcached extends AbstractClassFacade
{
	protected static $className = '\\PHPCrystal\\PHPCrystal\\Service\\Cache\\Driver\\Memcached\\Memcached';
}
