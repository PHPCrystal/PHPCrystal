<?php
namespace PHPCrystal\PHPCrystal\Component\Package;

use Composer\Installer\PackageEvent;
use PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper;
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
	
	/**
	 * 
	 */
	public function init()
	{
		parent::init();
		$this->addEventListener(Event\Type\System\ExtensionInstall::toType(), function($event) {
			if ($this->getComposerName() == $event->getComposerPackageName()) {
				$composerLock = FileHelper::create($this->getApplication()->getDirectory(),
					'composer.lock');
				
				$composerLockJson = $composerLock->readJson();
				$currentPkgEntry = null;
				foreach ($composerLockJson['packages'] as $pkgEntry) {
					if ($pkgEntry['name'] == $this->getComposerName()) {
						$currentPkgEntry = &$pkgEntry;
						break;
					}
				}

				if (isset($currentPkgEntry['installed']) && 'yes'  === $currentPkgEntry['installed']) {
					return;
				}

				$success = $this->onInstall($event);
				if ($success) {
					$currentPkgEntry['install'] = 'yes';
					$composerLock->write($composerLockJson);
				}
			}
		});
	}

	//
	// Event hooks
	//

	/**
	 * Installs an extension.
	 * 
	 * @return boolean
	 */
	protected function onInstall(Event\Type\System\ExtensionInstall $event)
	{
		return true;
	}
}
