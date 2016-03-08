<?php
// Application config container
namespace PHPCrystal\PHPCrystal\Component\Package\Config;

use PHPCrystal\PHPCrystal\Component\Container\AbstractConfig,
	PHPCrystal\PHPCrystal\Component\Service\Config as ServiceConfig;

class Config extends AbstractConfig
{
	/**
	 * @return PHPCrystal\PHPCrystal\Component\Service\Config
	 */
	public function service($name)
	{
		$serviceCfg = ServiceConfig::create();
		$this->set($name, $serviceCfg);
		
		return $serviceCfg;
	}
}
