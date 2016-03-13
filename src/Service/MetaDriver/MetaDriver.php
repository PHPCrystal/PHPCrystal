<?php
namespace PHPCrystal\PHPCrystal\Service\MetaDriver;

use PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper;
use PHPCrystal\PHPCrystal\Component\Service\AbstractService,
	PHPCrystal\PHPCrystal\Component\Exception\System\CompileTimeError,
	PHPCrystal\PHPCrystal\Component\Service\AbstractContractor;
use PHPCrystal\PHPCrystal\Component\Package\AbstractExtension;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use PHPCrystal\PHPCrystal\Component\Php as Php;

const EXTENDABLE_INTERFACE = 'PHPCrystal\\PHPCrystal\\Component\\Factory\\ExtendableInterface';

final class MetaDriver extends AbstractService
{
	/** @var array */
	private $URI_classNameMapping = [];
	
	/** @var array */
	private $metaServices = [];	
	
	/** @var array */
	private $metaActions = [];
	
	/** @var array */
	private $metaControllers = [];
	
	/** @var array */
	private $metaFrontControllers = [];

	private $isVoid = true;
	private $data;
	private $filename;
	
	/** @var SimpleAnnotationReader */
	private $annotReader;
	
	/**
	 * @return bool
	 */
	public function isExtendable($className)
	{
		return Php\Aux::implementsInterface($className,
			'PHPCrystal\\PHPCrystal\\Service\\MetaDriver\\ExtendableInterface');
	}

	/**
	 * @return void
	 */
	public function addURI_classNameMappingEntry($URI_str, $className)
	{
		if (empty($URI_str)) {
			return;
		}
		
		if ( ! isset($this->URI_classNameMapping[$URI_str])) {
			$this->URI_classNameMapping[$URI_str] = [];
		}
		
		$this->URI_classNameMapping[$URI_str][] = $className;
	}
	
	/**
	 * @return array
	 */
	public function getClassNameMappingEntriesBy_URI($search_URI)
	{
		foreach ($this->URI_classNameMapping as $class_URI => $classNamesArray) {
			if ($class_URI == $search_URI) {
				return $classNamesArray;
			}
		}		
	}
	
	/**
	 * @return string
	 */
	public function getClassNameBy_URI($search_URI)
	{
		return $this->getClassNameMappingEntriesBy_URI($search_URI)[0];
	}
	
	/**
	 * @return void
	 */
	public function addMetaService($className, $package)
	{
		$metaService = new MetaClass\Service($className, $package->getPriority());
		$key = $metaService->getKey();

		if ( ! isset($this->metaServices[$key])) {
			$this->metaServices[$key] = new \SplPriorityQueue();
			$this->metaServices[$key]->setExtractFlags(\SplPriorityQueue::EXTR_DATA);
		}

		$this->metaServices[$key]->insert($metaService, $metaService->getPriority());
	}
	
	/**
	 * @return void
	 */
	public function addMetaAction($className, $package)
	{
		$metaAction = new MetaClass\Action($this->extendableResolveClassName($className),
			$this->getAnnotations($className), $className);		
		$key = $package->getFullName();
		
		if ( ! isset($this->metaActions[$key])) {
			$this->metaActions[$key] = [];
		} 
		
		$this->metaActions[$key][] = $metaAction;
	}
	
	/**
	 * @return void
	 */
	public function addMetaController($className, $package)
	{
		$metaController = new MetaClass\Controller($this->extendableResolveClassName($className),
			$this->getAnnotations($className), $className);
		$key = $package->getFullName();
		
		if ( ! isset($this->metaControllers[$key])) {
			$this->metaControllers[$key] = [];
		} 
		
		$this->metaControllers[$key][] = $metaController;	
	}
	
	/**
	 * @return void
	 */
	public function addMetaFrontController($className, $package)
	{
		$meta_FC = new MetaClass\FrontController($this->extendableResolveClassName($className),
			$this->getAnnotations($className), $className);
		$key = $package->getFullName();
		
		if ( ! isset($this->metaFrontControllers[$key])) {
			$this->metaFrontControllers[$key] = [];
		} 
		
		$this->metaFrontControllers[$key][] = $meta_FC;		
	}
	
	/**
	 * @return AbstarctMetaClass
	 */
	public function findMetaClassBy_URI($find_URI)
	{
		foreach (array_merge($this->metaActions, $this->metaControllers,
			$this->metaFrontControllers, $this->metaServices) as $key_URI => $metaClass) {
			if ($key_URI == $find_URI) {
				return $metaClass;
			}
		}
	}

	/**
	 * @return string
	 */
	private function extendableResolveClassName($base)
	{
		$appNS = $this->getApplication()->getNamespace();
		$extended = "\\$appNS\\Ext";
		$extended .= Php\Aux::isFullyQualifiedName($base) ? "$base" : "\\$base";
		
		return class_exists($extended) ? $extended : $base;
	}
	
	/**
	 * @return array
	 */
	private function mergeAnnots(...$annotArrays)
	{
		$result = array();
		$merged = array_merge(...$annotArrays);
		
		foreach ($merged as $annot) {
			$key = get_class($annot);
			
			if (isset($result[$key])) {
				$result[$key]->merge($annot);
			} else {
				$result[$key] = $annot;
			}
		}
		
		return array_values($result);
	}
	
	/**
	 * @return array
	 */
	private function getAnnotations($base)
	{
		$baseAnnots = $this->annotReader->getClassAnnotations(new \ReflectionClass($base));
		$extended  = $this->extendableResolveClassName($base);

		if ($base == $extended) {
			return $this->mergeAnnots($baseAnnots);
		}
		
		// fetch annotations of the extended class and merge them with the annotions
		// of the base one
		$extendedAnnots = $this->annotReader->getClassAnnotations(new \ReflectionClass($extended));
		
		return $this->mergeAnnots($baseAnnots, $extendedAnnots);
	}

	/**
	 * @return string
	 */
	public function classNametoDotname($name)
	{
		return ltrim(strtolower(str_replace('\\', '.', $name)), '.');
	}

	/**
	 * @return Metaservice
	 */
	public function getMetaServiceByInterface($name)
	{
		foreach ($this->metaServices as $splQueue) {
			$metaService = $splQueue->top();

			if ($metaService->check($name)) {
				return $metaService;
			}
		}

		throw new \RuntimeException(sprintf('Required service "%s" has not been found', $name));
	}

	/**
	 * @return null
	 */
	public function init()
	{
		parent::init();
		$this->annotReader = new SimpleAnnotationReader();
		$this->annotReader->addNamespace(__NAMESPACE__ . '\\Annotation\\Action');
		$this->annotReader->addNamespace(__NAMESPACE__ . '\\Annotation\\Common');
	}

	/**
	 * @return string
	 */
	public function extractPackageNS($name)
	{
		$parts = explode('\\', $name);

		return $parts[0] . '\\' . $parts[1];
	}

	/**
	 * @return object
	 */
	public function getOwnerInstance($mixed)
	{
		$pkgNS = $this->getOwnerNS($mixed);

		foreach ($this->getApplication()->getExtensions(true) as $pkg) {
			if ($pkg->getNamespace() == $pkgNS) {
				return $pkg;
			}
		}
	}

	/**
	 * Returns package namespace from which the given object's parent class originates
	 * 
	 * @return string
	 */
	public function getOwnerNS($mixed)
	{
		$refClass = new \ReflectionClass($mixed);

		while ($refClass) {
			$ownerNS = $this->extractPackageNS($refClass->getNamespaceName());

			if ($this->getApplication()->getNamespace() != $ownerNS ||
				$refClass->isAbstract()) {
				return $ownerNS;
			}

			$refClass = $refClass->getParentClass();
		}
	}

	/**
	 * Returns a package instance by the name of one of its classes
	 * 
	 * @return \PHPCrystal\PHPCrystal\Component\Package\AbstractPackage
	 */
	public function getPackageByItsMember($mixed)
	{
		$pkgNamespace = $this->getPackageNamespaceByItsMemeber($mixed);

		foreach ($this->getApplication()->getExtensions(true) as $pkg) {
			if ($pkgNamespace == $pkg->getNamespace()) {
				return $pkg;
			}
		}
	}

	public function getPackageByName($pkgName)
	{
		foreach ($this->getApplication()->getExtensions(true) as $pkg) {
			if ($pkgName == $pkg->getComposerName()) {
				return $pkg;
			}
		}
	}

	/**
	 * @return \PHPCrystal\PHPCrystal\Service\Metadriver\ExtendableController
	 */
	public function getControllerMetaClassByAction($action)
	{
		$parts = explode('\\', get_class($action));
		$pkgInstance = $this->getPackageByItsMember($action);

		$baseClass = $pkgInstance->getNamespace() . '\\Controller\\' .
			$parts[3] . '\\' . $parts[4];

		$pkgControllers = $this->data['controllers'][$pkgInstance->getKey()];

		foreach ($pkgControllers as $metaClass) {
			if ($metaClass->getBaseClass() == $baseClass) {
				return $metaClass;
			}
		}
	}

	/**
	 * @return \PHPCrystal\PHPCrystal\Service\Metadriver\ExtendableFrontController
	 */
	public function getFrontControllerMetaClassByAction($action)
	{
		$parts = explode('\\', get_class($action));
		$pkgInstance = $this->getPackageByItsMember($action);

		$baseClass = $pkgInstance->getNamespace() . '\\FrontController\\' .
			$parts[3];

		$pkgControllers = $this->data['frontcontrollers'][$pkgInstance->getKey()];

		foreach ($pkgControllers as $metaClass) {
			if ($metaClass->getBaseClass() == $baseClass) {
				return $metaClass;
			}
		}
	}

	/**
	 * @todo remove
	 * @return string
	 */
	public function getPackageNamespaceByItsMemeber($mixed)
	{
		$className = is_object($mixed) ? get_class($mixed) : $mixed;

		$parts = explode('\\', $className);
		$pkgNamespace = $parts[0] . '\\' . $parts[1];

		return $pkgNamespace;
	}

	/**
	 * @return mixed
	 */
	protected function getData($key)
	{
		return $this->data[$key];
	}

	/**
	 * @return void
	 */
	public function addService($metaservice)
	{
		$this->data['export'][] = $metaservice;
	}

	/**
	 * @return void
	 */
	public function addExtensionsToAutoload()
	{
		$composer_lock = FileHelper::create('@app/composer.lock');

		if (!$composer_lock->fileExists()) {
			return;
		}

		$composer_json = $composer_lock->readJson();
		foreach ($composer_json['packages'] as $pkgInfo) {
			$pkgName = $pkgInfo['name'];
			$pkgBootstrap = FileHelper::create('@app/vendor/', $pkgName, 'bootstrap.php');
			if (!$pkgBootstrap->fileExists()) {
				continue;
			}

			$pkg_instance = $pkgBootstrap->_require();
			if (!$pkg_instance instanceof AbstractExtension ||
				$pkg_instance->getDisableAutoloadFlag()) {
				continue;
			}

			$this->data['extensions'][] = new MetaExtension($pkgBootstrap->getDirname());
		}
	}

	/**
	 * @return MetaExtension[]
	 */
	public function getExtensionsAll()
	{
		return $this->data['extensions'];
	}

	/**
	 * @return array
	 */
	public function getContractors()
	{
		return $this->data['export'];
	}

	protected function addPackageMetaClasses($package, array $metaClassColl, $category)
	{
		$key = $package->getKey();

		if (!isset($this->data[$category][$key])) {
			$this->data[$category][$key] = array();
		}

		$this->data[$category][$key] = array_merge($this->data[$category][$key], $metaClassColl);

		return $this;
	}

	/**
	 * @return $this 
	 */
	public function addPackageActions($package, array $metaClassColl)
	{
		return $this->addPackageMetaClasses($package, $metaClassColl, 'actions');
	}

	/**
	 * 
	 */
	public function addPackageControllers($package, array $metaClassColl)
	{
		return $this->addPackageMetaClasses($package, $metaClassColl, 'controllers');
	}

	/**
	 * 
	 */
	public function addPackageFrontControllers($package, array $metaClassColl)
	{
		return $this->addPackageMetaClasses($package, $metaClassColl, 'frontcontrollers');
	}

	/**
	 * @return array
	 */
	public function getPackageActions($package)
	{
		$key = $package->getKey();

		return isset($this->data['actions'][$key]) ?
			$this->data['actions'][$key] : array();
	}

	/**
	 * @return array
	 */
	public function getActionsAll()
	{
		return $this->data['actions'];
	}

	/**
	 * @return null
	 */
	public function findMetaClassByBaseClass($baseClass)
	{
		$arVal1 = array_values($this->data['actions']);
		$actionMetaClasses = empty($arVal1) ?
			[] : array_merge(...$arVal1);

		$arVal2 = array_values($this->data['controllers']);
		$ctrlMetaClasses = empty($arVal2) ?
			[] : array_merge(...$arVal2);

		$arVal3 = array_values($this->data['frontcontrollers']);
		$fcMetaClasses = empty($arVal3) ?
			[] : array_merge(...$arVal3);

		$haystack = array_merge($actionMetaClasses, $ctrlMetaClasses, $fcMetaClasses);

		foreach ($haystack as $item) {
			if ($item->getBaseClass() == $baseClass) {
				return $item;
			}
		}

		return null;
	}

	public function flush()
	{
		$this->data = array(
			'export' => [],
			'actions' => [],
			'controllers' => [],
			'frontcontrollers' => [],
			'extensions' => []
		);
	}

	/**
	 * @return null
	 */
	public function save()
	{
		$this->filename->serialize($this->data);
	}

	/**
	 * @return boolean
	 */
	public function isVoid()
	{
		return $this->isVoid;
	}

}
