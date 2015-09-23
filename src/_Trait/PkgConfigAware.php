<?php
namespace PHPCrystal\PHPCrystal\_Trait;

use PHPCrystal\PHPCrystal\Facade\Metadriver;

trait PkgConfigAware
{
	private $config;

	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Package\Config
	 */
	public function getMergedConfig()
	{
		$originPkgInstance = Metadriver::getOwnerInstance($this);
		$mergedConfig = clone $originPkgInstance->getConfig();		
		$originPkgDotName = $originPkgInstance->getComposerName(true);

		$appConfig = $this->getApplication()
			->getConfig();
		
		$mergedConfig->merge($appConfig);
		
		return $mergedConfig;
	}

	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Package\Config
	 */
	public function getConfig()
	{
		return $this->config;
	}
}
