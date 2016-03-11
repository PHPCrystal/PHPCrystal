<?php
namespace PHPCrystal\PHPCrystal\Service\PackageBuilder;

use PHPCrystal\PHPCrystal\Component\MVC as MVC,
	PHPCrystal\PHPCrystal\Component\Php as Php,
	PHPCrystal\PHPCrystal\Component\Service\AbstractService,
	PHPCrystal\PHPCrystal\Component\Service\AbstractContractor;

class PackageBuilder extends AbstractBuilder
{
	/**
	 * @return void
	 */
	protected function exportMetaClasses()
	{
		$this->scanPhpDefinitions('src/Service', function($className) {
			if (AbstractContractor::is_subclass($className)) {
				$this->metadriver->addMetaService($className, $this->getPackage());
			}
			
			if (AbstractService::is_subclass($className)) {
				$class_URI = $this->getFactory()
					->getNameResolver()
					->toService_URI($className);			
				$this->metadriver->addClassName_URI_MappingEntry($className, $class_URI);
			}
		});

		$this->scanPhpDefinitions('src/Action', function($className) {
			if (MVC\Controller\Action\AbstractAction::is_subclass($className)) {
				$this->metadriver->addMetaAction($className);
			}
		});
		
		$this->scanPhpDefinitions('src/Controller',function($className) {
			if (MVC\Controller\AbstractController::is_subclass($className)) {
				$this->metadriver->addMetaController($className);
			}
		});

		$this->scanPhpDefinitions('src/FrontController', function($className) {
			if (MVC\Controller\AbstractFrontController::is_subclass($className)) {
				$this->metadriver->addMetaFrontController($className);
			}
		});
	}
	
	protected function prepareNamesMapping()
	{
		$this->scanPhpDefinitions('src', function($className) {
			if (AbstractService::is_subclass($className)
				&& Php\Aux::isInstantiable($className)) {
				$class_URI = $this->getFactory()
					->getNameResolver()
					->toService_URI($className);			
				$this->metadriver->addClassName_URI_MappingEntry($className, $class_URI);
			}
		}, ['vendor']);
	}

	/**
	 * @return void
	 */
	public function run()
	{
		$this->prepareNamesMapping();
		$this->exportMetaClasses();
	}
}
