<?php
namespace PHPCrystal\PHPCrystal\Service\PackageBuilder;

use PHPCrystal\PHPCrystal\Facade as Facade;

class PackageBuilder extends AbstractBuilder
{
	/**
	 * @return void
	 */
	private function exportContractors()
	{
		// application package do not export any service
		if ($this->getApplication() === $this->getPackage()) {
			return;
		}

		$matadriver = Facade\Metadriver::create();
		foreach ($this->getContractors() as $metaservice) {
			$matadriver->addService($metaservice);
		}		
	}

	/**
	 * @return void
	 */
	private function exportExtendables()
	{
		$metadriver = Facade\Metadriver::create();
		
		$metadriver
			->addPackageActions($this->getPackage(), $this->getActions())
			->addPackageControllers($this->getPackage(), $this->getControllers())
			->addPackageFrontControllers($this->getPackage(), $this->getFrontControllers());		
	}

	/**
	 * @return void
	 */
	public function run()
	{
		$this->exportContractors();
		$this->exportExtendables();
	}
}
