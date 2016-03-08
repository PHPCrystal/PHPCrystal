<?php

namespace PHPCrystal\PHPCrystal\Service\PackageBuilder;

use PHPCrystal\PHPCrystal\Component\Service\MetaService,
	PHPCrystal\PHPCrystal\Component\Service\AbstractService,
	PHPCrystal\PHPCrystal\Component\Service\AbstractContractor,
	PHPCrystal\PHPCrystal\Component\Filesystem\Finder,
	PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper,
	PHPCrystal\PHPCrystal\Component\Php\Parser,
	Doctrine\Common\Annotations\AnnotationReader,
	PHPCrystal\PHPCrystal\Service\Metadriver as Metadriver,
	PHPCrystal\PHPCrystal\Component\Exception as Exception;

const EVENT_MODEL_ABSTRACT_NODE_CLASS = 'PHPCrystal\PHPCrystal\Service\Event\AbstractNode';

class PackageBuilder extends AbstractBuilder
{
	/**
	 * Returns an array of service contractors
	 * 
	 * @return array
	 */
	public function getContractors()
	{
		$scanResult = $this->scanPhpDefinitions('src/Service', function($className) {
			if (AbstractContractor::isContractor($className)) {
				return new MetaService($className, $this->getPackage()->getPriority());
			}
		});
	
		return $scanResult;
	}

	/**
	 * Returns an array of package extendable classes
	 * 
	 * @param string $relPathname Relative path to a directory with extendable classes
	 * @param string $metaClassName Name of an extendable meta class
	 * @return array
	 */
	protected function getPackageExtendables($relPathname, $metaClassName)
	{
		$result = array();

		$app = $this->getApplication();
		$pkg_root_dir = $this->getPackage()->getDirectory();

		$scan_dir = FileHelper::create($pkg_root_dir, 'src', $relPathname);
		if (!$scan_dir->dirExists()) {
			return $result;
		}

		$php_files = Finder::create()->findPhpFiles($scan_dir->toString());
		foreach ($php_files as $file) {
			$base_class = Parser::loadFromFile($file->getRealpath())
				->parseClass();

			// all package extendable classes are derived from the AbstractNode
			// class if not then it's something else
			if (!is_subclass_of($base_class, EVENT_MODEL_ABSTRACT_NODE_CLASS)) {
				continue;
			}

			if ($app === $this->getPackage()) {
				$result[] = new $metaClassName($base_class, null);
				continue;
			}

			$extended = Metadriver\Metadriver::getExtendedClassNameByBase($base_class);
			if (class_exists($extended)) {
				$result[] = new $metaClassName($base_class, $extended);
			} else {
				$result[] = new $metaClassName($base_class, null);
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected function getActions()
	{
		$extendable_actions = $this->getPackageExtendables('Action', '\\PHPCrystal\\PHPCrystal\\Service\\Metadriver\\ExtendableAction');

		// check whether controller method is callable
		foreach ($extendable_actions as $action_meta_class) {
			$target = $action_meta_class->getTargetClass();

			$ctrl_method_name = $action_meta_class->getControllerMethodAnnotation()
				->getMethodName();
			$ctrl_callback = [$target::getControllerClassName(), $ctrl_method_name];
			if (!is_callable($ctrl_callback)) {
				Exception\System\FrameworkRuntimeError::create('Controller method "%s::%s" isn\'t callable', null, $ctrl_callback[0], $ctrl_callback[1])
					->_throw();
			}
		}

		return $extendable_actions;
	}

	/**
	 * @return array
	 */
	protected function getControllers()
	{
		$extendable_ctrls = $this->getPackageExtendables('Controller', '\\PHPCrystal\\PHPCrystal\\Service\\Metadriver\\ExtendableController');

		return $extendable_ctrls;
	}

	/**
	 * @return array
	 */
	protected function getFrontControllers()
	{
		$extendable_fcs = $this->getPackageExtendables('FrontController', '\\PHPCrystal\\PHPCrystal\\Service\\Metadriver\\ExtendableFrontController');

		return $extendable_fcs;
	}

	/**
	 * @return void
	 */
	private function exportContractors()
	{
		$metadriver = $this->getFactory()->getMetadriver();

		foreach ($this->getContractors() as $metaService) {
			$t = $metaService->getClassName();
			$metadriver->addMetaService($metaService);
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
		//$this->exportExtendables();
	}
}
