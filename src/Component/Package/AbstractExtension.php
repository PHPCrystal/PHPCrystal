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
		$pkgInstance = $event->getOperation()->getPackage();
		
		$extInstallEvent = Event\Type\System\ExtensionInstall::create($pkgInstance);
		
		$appPkg = require __DIR__ . '/../../../bootstrap.php';
		$appPkg->dispatch($extInstallEvent);
	}
}
