<?php
// Application config container
namespace PHPCrystal\PHPCrystal\Component\Service;

use PHPCrystal\PHPCrystal\Component\Container\AbstractConfig;

class Config extends AbstractConfig
{
	/**
	 * @return
	 */
	public function service($name)
	{
		$this->set()
	}
	
	/**
	 * 
	 */
	public function set($itemKey, $value)
	{
		if ($value instanceof AbstractSubcontractor) {
			
		}
		parent::set($itemKey, $value);
	}
}
