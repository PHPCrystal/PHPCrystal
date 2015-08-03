<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\System;

use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Service\Event\Context\Cli;

class ExtensionInstall extends Event\Type\AbstractExternal
{
	private $packageInstance;

	/**
	 * @api
	 */
	public function __construct(\Composer\Package\CompletePackage $pkgInstance)
	{
		parent::__construct();
		$this->packageInstance = $pkgInstance;
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
	 * @return \PHPCrystal\PHPCrystal\Service\Event\Context\Cli
	 */
	public function createContext()
	{
		return Cli::create('ExtInstallContext');
	}
}
