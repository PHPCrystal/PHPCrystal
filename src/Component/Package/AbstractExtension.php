<?php
namespace PHPCrystal\PHPCrystal\Component\Package;

use Composer\Installer\PackageEvent;
use PHPCrystal\PHPCrystal\Service\Event as Event;

abstract class AbstractExtension extends AbstractPackage 
{
	/**
	 * @var bool
	 */
	private $disabledFlag;
	
	/**
	 * @return bool
	 */
	public function getDisabledFlag()
	{
		return $this->disabledFlag;
	}
	
	/**
	 * @return $this
	 */
	final public function setDisabledFlag($flagValue)
	{
		$this->disabledFlag = $flagValue;
		
		return $this;
	}
	
	/**
	 * @return void
	 */
	public static function install(PackageEvent $event)
	{
		$composerJson = getenv('COMPOSER');		
		$appRootDir = empty($composerJson) ? getcwd() : dirname($composerJson);

		$appPkgInstance = require "{$appRootDir}/bootstrap.php";
		$composerPkg = $event->getOperation()->getPackage();

		$extInstallEvent = Event\Type\System\ExtensionInstall::create($composerPkg);

		$appPkgInstance->dispatch($extInstallEvent);
	}
}
