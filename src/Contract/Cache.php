<?php
namespace PHPCrystal\PHPCrystal\Contract;

interface Cache
{
	public function get($key, $defaultValue = null);
	
	public function set($key, $value);
}
