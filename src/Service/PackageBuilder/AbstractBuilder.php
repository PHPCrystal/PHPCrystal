<?php
namespace PHPCrystal\PHPCrystal\Service\PackageBuilder;

use PHPCrystal\PHPCrystal\Component\Service\MetaService;
use PHPCrystal\PHPCrystal\Component\Service\AbstractService;
use PHPCrystal\PHPCrystal\Component\Service\AbstractContractor;
use PHPCrystal\PHPCrystal\Component\Filesystem\Finder;
use PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper;
use PHPCrystal\PHPCrystal\Component\PhpParser\PhpParser;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPCrystal\PHPCrystal\Service\Metadriver as Metadriver;
use PHPCrystal\PHPCrystal\Component\Exception as Exception;

const EVENT_MODEL_ABSTRACT_NODE_CLASS = 'PHPCrystal\PHPCrystal\Service\Event\AbstractNode';

abstract class AbstractBuilder extends AbstractService
{
	private $annotReader;

	/**
	 * @return array
	 */
	protected function getContractDefinitions()
	{
		$result = array();
		$pkgDir = $this->getPackage()->getDirectory();

		$contractsDir = FileHelper::create($pkgDir, 'src', 'Contract');
		if ( ! $contractsDir->dirExists()) {
			return $result;
		}
		
		$phpFilesColl = Finder::create()
			->findPhpFiles($contractsDir->toString());
		
		foreach ($phpFilesColl as $file) {
			$interface = PhpParser::loadFromFile($file->getRealpath())
				->parseInterface();
			$result[] = $interface;
		}
		
		return $result;
	}

	/**
	 * @return array
	 */
	public function getExportedServices()
	{
		$result = array();

		$contractDefs = $this->getContractDefinitions();
		$pkgDir = $this->getPackage()->getDirectory();
		$serviceDir = FileHelper::create($pkgDir, 'src', 'Service');
		
		if ( ! $serviceDir->dirExists()) {
			return $result;
		}
		
		$phpFiles = Finder::create()->findPhpFiles($serviceDir->toString());		
		foreach ($phpFiles as $file) {
			$className = PhpParser::loadFromFile($file->getRealpath())
				->parseClass();

			if ( ! empty($className) && ! class_exists($className)) {
				continue;
			}

			if (null === ($interface = AbstractContractor::getContract($className, $contractDefs)) ||
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
	 * @return array
	 */
	protected function getExtendableMetaClasses($baseDir, $metaClassName)
	{
		$result = array();
		
		$appPkg = $this->getApplication();
		$pkgDir = $this->getPackage()->getDirectory();
		$extDir = FileHelper::create($appPkg->getDirectory(), 'Extension');

		$baseDir = FileHelper::create($pkgDir, 'src', $baseDir);
		if ( ! $baseDir->dirExists()) {
			return $result;
		}

		$phpFiles = Finder::create()->findPhpFiles($baseDir->toString());
		foreach ($phpFiles as $file) {
			$baseClass = PhpParser::loadFromFile($file->getRealpath())
				->parseClass();

			// all package extendable classes are derived from the AbstractNode
			// class. if not then it's something else
			if ( ! is_subclass_of($baseClass, EVENT_MODEL_ABSTRACT_NODE_CLASS)) {
				continue;
			}

			if ($appPkg === $this->getPackage()) {
				$result[]  = new $metaClassName($baseClass, null);
				continue;
			}

			$extended = Metadriver\Metadriver::getExtendedClassNameByBase($baseClass);

			if (class_exists($extended)) {
				$result[] = new $metaClassName($baseClass, $extended);				
			} else {
				$result[] = new $metaClassName($baseClass, null);				
			}
		}

		return $result;		
	}

	/**
	 * @return array
	 */
	protected function getActions()
	{
		$metaClassName = '\\PHPCrystal\\PHPCrystal\\Service\\Metadriver\\ExtendableAction';		
		$metaClasses = $this->getExtendableMetaClasses('Action', $metaClassName);

		// check whether controller method is callable
		foreach ($metaClasses as $metaClass) {
			$target = $metaClass->getTargetClass();

			$ctrlMethodName = $metaClass->getControllerMethodAnnotation()
				->getMethodName();
			$ctrlCallback = [$target::getControllerClassName(), $ctrlMethodName];

			if ( ! is_callable($ctrlCallback)) {
				Exception\System\FrameworkRuntimeError::create('Controller method "%s::%s" isn\'t callable',
					null, $ctrlCallback[0], $ctrlCallback[1])
					->_throw();
			}
		}

		return $metaClasses;
	}

	/**
	 * @return array
	 */
	protected function getControllers()
	{
		$metaClassName = '\\PHPCrystal\\PHPCrystal\\Service\\Metadriver\\ExtendableController';
		
		$metaClasses = $this->getExtendableMetaClasses('Controller', $metaClassName);

		return $metaClasses;
	}

	/**
	 * @return array
	 */
	protected function getFrontControllers()
	{
		$metaClassName = '\\PHPCrystal\\PHPCrystal\\Service\\Metadriver\\ExtendableFrontController';
		
		$metaClasses = $this->getExtendableMetaClasses('FrontController', $metaClassName);

		return $metaClasses;
	}
	
	/**
	 * @return void
	 */	
	public function init()
	{
		$this->annotReader = new AnnotationReader();
	}
	
	abstract public function run();
}
