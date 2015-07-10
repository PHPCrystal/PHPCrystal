<?php
namespace PHPCrystal\PHPCrystal\Contract;

interface Session
{
	public function get($key, $defaultValue);
	
	public function set($key, $value);
	
	public function has($key);
	
	public function getFlash($key);
	
	public function setFlash($key, $value);
	
	public function hasFlash($key);
	
	public function isActive();

	public function isAuthenticated();
	
	public function flush();
}
