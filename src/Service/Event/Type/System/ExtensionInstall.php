<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\System;

use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Service\Event\Context\Cli;

class ExtensionInstall extends Event\Type\AbstractExternal
{
	private $packageInstance;
	private $composerPackageName;

	/**
	 * @api
	 */
	public function __construct(\Composer\Package\CompletePackage $pkgInstance = null)
	{
		parent::__construct();
		$this->packageInstance = $pkgInstance;
		if ($pkgInstance) {
			$this->composerPackageName = $pkgInstance->getName();
		}
		$this->type = Event\TYPE_BROADCAST_POST_ORDER;
	}

	/**
	 * @return \Composer\Package\CompletePackage
	 */
	public function getComposerPackageInstance()
	{
		return $this->packageInstance;
	}
	
	/**
	 * Returns composer package name
	 * 
	 * @return string
	 */
	public function getComposerPackageName()
	{
		return $this->composerPackageName;
	}
	
	/**
	 * @return $this
	 */
	public function setComposerPackageName($pkgName)
	{
		$this->composerPackageName = $pkgName;
		
		return $this;
	}

	/**
	 * @return \PHPCrystal\PHPCrystal\Service\Event\Context\Cli
	 */
	public function createContext()
	{
		$context =  Cli::create('ExtInstallContext');
		$context->setEnv('dev');
		
		return $context;
	}
}
