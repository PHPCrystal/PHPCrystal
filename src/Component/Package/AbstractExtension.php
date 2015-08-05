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
	 * @return void
	 */
	protected function onInstallHelper(Event\Type\System\ExtensionInstall $event)
	{
		if ($this->getComposerName() != $event->getComposerPackageName()) {
			return;
		}

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

		// install extension and mark it as installed
		$success = $this->onInstall($event);
		if ($success) {
			$currentPkgEntry['installed'] = 'yes';
			$composerLock->writeJson($composerLockJson,
				JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		}
	}
	
	/**
	 * @return $this
	 */
	public function init()
	{
		parent::init();

		// extension installation event listener
		$this->addEventListener(Event\Type\System\ExtensionInstall::toType(), function($event) {
			$this->onInstallHelper($event);
		});
		
		return $this;
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
