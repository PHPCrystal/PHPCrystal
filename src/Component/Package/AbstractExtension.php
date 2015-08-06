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
	private $disableAutoloadFlag;
	
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
	 * @return bool
	 */
	final public function getDisableAutoloadFlag()
	{
		return $this->disableAutoloadFlag;
	}

	/**
	 * @return $this
	 */
	final public function setDisableAutoloadFlag($value)
	{
		$this->disableAutoloadFlag = $value;

		return $this;
	}
	
	/**
	 * @return void
	 */
	public static function install(PackageEvent $event)
	{
		global $app;
		
		$composer_json_filename = getenv('COMPOSER');
		$app_root_dir = empty($composer_json_filename) ? getcwd() : dirname($composer_json_filename);

		$app = require "{$app_root_dir}/bootstrap.php";
		$composer_pkg = $event->getOperation()->getPackage();

		$vendor_dir = getenv('COMPOSER_VENDOR_DIR');
		$vendor_dir_abs = empty($vendor_dir) ? "{$app_root_dir}/vendor" : "{$app_root_dir}/{$vendor_dir}";

		$composer_pkg_dir = $vendor_dir_abs . DIRECTORY_SEPARATOR . $composer_pkg->getName();
		$app->addExtension($composer_pkg_dir);

		$ext_install_event = Event\Type\System\ExtensionInstall::create($composer_pkg);
		$app->dispatch($ext_install_event);		
	}
	
	/**
	 * @return void
	 */
	public static function finishInstallation(Event\Type\System\ExtensionInstall $event)
	{
		$app = $event->getCurrentNode()->getRootNode();

		$composer_lock = FileHelper::create($app->getDirectory(), 'composer.lock');
		$composer_lock_json = $composer_lock->readJson();

		foreach ($composer_lock_json['packages'] as &$pkg_entry) {
			if ($pkg_entry['name'] == $event->getComposerPackageName() &&
				true === $event->getResult())
			{
				// Mark extension as installed
				$pkg_entry['installed'] = 'yes';
				$composer_lock->writeJson($composer_lock_json,
					JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);				
			}
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
			return $this->onInstall($event);
		});
		
		return $this;
	}

	//
	// Event hooks
	//

	/**
	 * Composer on-autoload-dump event listener
	 * 
	 * @return void
	 */
	public static function onPostAutoloadDump()
	{
		global $app;

		if ( ! $app || null === ($current_event = $app->getCurrentEvent())) {
			return;
		}

		if ($current_event instanceof Event\Type\System\ExtensionInstall) {
			self::finishInstallation($current_event);
		}
	}

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
