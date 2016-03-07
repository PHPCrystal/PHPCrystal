<?php
namespace PHPCrystal\PHPCrystal\Service\PackageBuilder;

use PHPCrystal\PHPCrystal\Component\Service\MetaService;
use PHPCrystal\PHPCrystal\Component\Service\AbstractService;
use PHPCrystal\PHPCrystal\Component\Service\AbstractContractor;
use PHPCrystal\PHPCrystal\Component\Filesystem\Finder;
use PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper;
use PHPCrystal\PHPCrystal\Component\Php\Parser;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPCrystal\PHPCrystal\Service\Metadriver as Metadriver;
use PHPCrystal\PHPCrystal\Component\Exception as Exception;

const EVENT_MODEL_ABSTRACT_NODE_CLASS = 'PHPCrystal\PHPCrystal\Service\Event\AbstractNode';

abstract class AbstractBuilder extends AbstractService
{
	private $annotReader;

	/**
	 * Returns an array of service contract definition
	 * 
	 * @return array
	 */
	protected function getContractSpecs()
	{
		$result = array();

		$targetDir = FileHelper::create($this->getPackage()->getDirectory(), 'src', 'Contract');
		if ( ! $targetDir->dirExists()) {
			return $result;
		}
		
		$phpFiles = Finder::create()->findPhpFiles($targetDir->toString());
		foreach ($phpFiles as $file) {
			$interface = Parser::loadFromFile($file->getRealpath())
				->parseInterface();
			$result[] = $interface;
		}

		return $result;
	}

	/**
	 * Returns an array of service contractors
	 * 
	 * @return array
	 */
	public function getContractors()
	{
		$result = array();
		$contractSpecs = $this->getContractSpecs();

		$targetDir = FileHelper::create($this->getPackage()->getDirectory(), 'src', 'Service');		
		if ( ! $targetDir->dirExists()) {
			return $result;
		}

		$phpFiles = Finder::create()->findPhpFiles($targetDir->toString());		
		foreach ($phpFiles as $file) {
			$className = Parser::loadFromFile($file->getRealpath())
				->parseClass();

			if ( ! empty($className) && ! class_exists($className)) {
				continue;
			}

			if (null === ($interface = AbstractContractor::getContract($className, $contractSpecs)) ||
				! AbstractContractor::isContractor($className))
			{
				continue;
			}

			$result[] = new MetaService($className, $interface,
				$this->getPackage()->getPriority());
		}

		return $result;
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
		if ( ! $scan_dir->dirExists()) {
			return $result;
		}

		$php_files = Finder::create()->findPhpFiles($scan_dir->toString());
		foreach ($php_files as $file) {
			$base_class = Parser::loadFromFile($file->getRealpath())
				->parseClass();

			// all package extendable classes are derived from the AbstractNode
			// class if not then it's something else
			if ( ! is_subclass_of($base_class, EVENT_MODEL_ABSTRACT_NODE_CLASS)) {
				continue;
			}

			if ($app === $this->getPackage()) {
				$result[]  = new $metaClassName($base_class, null);
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
		$extendable_actions = $this->getPackageExtendables('Action',
			'\\PHPCrystal\\PHPCrystal\\Service\\Metadriver\\ExtendableAction');

		// check whether controller method is callable
		foreach ($extendable_actions as $action_meta_class) {
			$target = $action_meta_class->getTargetClass();

			$ctrl_method_name = $action_meta_class->getControllerMethodAnnotation()
				->getMethodName();
			$ctrl_callback = [$target::getControllerClassName(), $ctrl_method_name];
			if ( ! is_callable($ctrl_callback)) {
				Exception\System\FrameworkRuntimeError::create('Controller method "%s::%s" isn\'t callable',
					null, $ctrl_callback[0], $ctrl_callback[1])
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
		$extendable_ctrls = $this->getPackageExtendables('Controller',
			'\\PHPCrystal\\PHPCrystal\\Service\\Metadriver\\ExtendableController');

		return $extendable_ctrls;
	}

	/**
	 * @return array
	 */
	protected function getFrontControllers()
	{
		$extendable_fcs = $this->getPackageExtendables('FrontController',
			'\\PHPCrystal\\PHPCrystal\\Service\\Metadriver\\ExtendableFrontController');

		return $extendable_fcs;
	}

	/**
	 * @return void
	 */	
	public function init()
	{
		$this->annotReader = new AnnotationReader();
	}

	//
	// Abstract methods
	//

	abstract public function run();
}
