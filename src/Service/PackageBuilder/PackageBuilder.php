<?php
namespace PHPCrystal\PHPCrystal\Service\PackageBuilder;

use PHPCrystal\PHPCrystal\Component\MVC as MVC,
	PHPCrystal\PHPCrystal\Component\Php as Php,
	PHPCrystal\PHPCrystal\Component\Exception\System\CompileTimeError,
	PHPCrystal\PHPCrystal\Service\MetaDriver as MetaDriver,
	PHPCrystal\PHPCrystal\Component\Service\AbstractService,
	PHPCrystal\PHPCrystal\Component\Service\AbstractContractor;

class PackageBuilder extends AbstractBuilder
{
	/**
	 * @return void
	 */
	public function run()
	{
		$this->prepareNamesMapping();
		$this->exportMetaClasses();
	}

	/**
	 * @return void
	 */
	protected function exportMetaClasses()
	{
		$this->scanPhpDefinitions('src/Service', function($className) {
			if (AbstractContractor::isSubclass($className)) {
				$this->metadriver->addMetaService($className, $this->getPackage());
			}
		});

		$this->scanPhpDefinitions('src/Action', function($className) {
			if (MVC\Controller\Action\AbstractAction::isSubclass($className)) {
				$this->metadriver->addMetaAction($className, $this->getPackage());
			}
		});
		
		$this->scanPhpDefinitions('src/Controller',function($className) {
			if (MVC\Controller\AbstractController::isSubclass($className)) {
				$this->metadriver->addMetaController($className, $this->getPackage());
			}
		});

		$this->scanPhpDefinitions('src/FrontController', function($className) {
			if (MVC\Controller\AbstractFrontController::isSubclass($className)) {
				$this->metadriver->addMetaFrontController($className, $this->getPackage());
			}
		});
	}
	
	/**
	 * @return void
	 */
	protected function prepareNamesMapping()
	{
		$resolver = $this->getFactory()->getNameResolver();

		$this->scanPhpDefinitions('src/Ext', function($extended) use($resolver) {			
			if ( ! $this->metadriver->isExtendable($extended)) {
				CompileTimeError::create('Class `%s` must implement <ExtendableInterface>',
					[$extended])
					->_throw();
			}

			// follow inheritence path of the extended class upto the abstract
			// class and add mapping entry of the current class URI to the name
			// of the extended class
			while (($parentClass = Php\Aux::getInstantiableParentClass($extended)) != null) {				
				$parent_URI = $resolver->classNameTo_URI($parentClass);
				$this->metadriver->addURI_classNameMappingEntry($parent_URI, $extended);				
			}
		});
		
		$this->scanPhpDefinitions('src', function($className) use($resolver) {
			if (empty($className) || ! Php\Aux::isInstantiable($className)) {
				return;
			}

			$URI_str = $resolver->classNameTo_URI($className);			
			$this->metadriver->addURI_classNameMappingEntry($URI_str, $className);
		}, ['Ext', 'Component']);
	}
}
