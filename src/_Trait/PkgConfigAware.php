<?php
namespace PHPCrystal\PHPCrystal\_Trait;

use PHPCrystal\PHPCrystal\Facade\Metadriver;

trait PkgConfigAware
{
	private $config;

	/**
	 * @return AbstractPackage
	 */
	private function getOwnerPackageInstance()
	{
		$ownerPkg = Metadriver::getPackageByItsMember($this);

		return $ownerPkg;
	}

	/**
	 * Merges the config file of the current package with the config file of the
	 * given package.
	 * 
	 * @return \PHPCrystal\PHPCrystal\Component\Package\Config
	 */
	public function getMergedConfig($ownerPkgName = null)
	{
		$pkgConfig = $this->getPackage()->getConfig();

		$pkgOwnerInstance = empty($ownerPkgName) ?
			$this->getOwnerPackageInstance() : Metadriver::getPackageByName($ownerPkgName);

		$pkgOwnerConfig = clone $pkgOwnerInstance->getConfig();
		$pkgOwnerConfig->merge($pkgConfig);

		return $pkgOwnerConfig;
	}

	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Package\Config
	 */
	public function getConfig()
	{
		return $this->config;
	}
}
