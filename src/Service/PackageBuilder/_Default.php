<?php
namespace PHPCrystal\PHPCrystal\Service\PackageBuilder;

use PHPCrystal\PHPCrystal\Facade as Facade;

class _Default extends AbstractBuilder
{
	private function dumpExportedServices()
	{
		// application package do not export any service
		if ($this->getApplication() === $this->getPackage()) {
			return;
		}

		foreach ($this->getExportedServices() as $metaservice) {
			Facade\Metadriver::addService($metaservice);
		}		
	}
	
	private function dumpMvcExtendable()
	{
		Facade\Metadriver::addPackageActions($this->getPackage(), $this->getActions())
			->addPackageControllers($this->getPackage(), $this->getControllers())
			->addPackageFrontControllers($this->getPackage(), $this->getFrontControllers());		
	}
	
	public function run()
	{
		$this->dumpExportedServices();
		$this->dumpMvcExtendable();
	}
}
