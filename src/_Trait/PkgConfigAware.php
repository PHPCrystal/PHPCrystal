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
		$pkgOwnerInstance = Metadriver::getOwnerInstance($this);
		$pkgOwnerConfig = clone $pkgOwnerInstance->getConfig();		
		$pkgOwnerDotName = $pkgOwnerInstance->getComposerName(true);

		$appConfig = $this->getApplication()
			->getConfig()->pluck($pkgOwnerDotName, true);
		
		return $pkgOwnerConfig->merge($appConfig);
	}

	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Package\Config
	 */
	public function getConfig()
	{
		return $this->config;
	}
}
