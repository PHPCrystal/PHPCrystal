<?php
namespace PHPCrystal\PHPCrystal\Service\Metadriver;

use PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper;
use PHPCrystal\PHPCrystal\Component\Service\AbstractService;
use PHPCrystal\PHPCrystal\Component\Package\AbstractExtension;
use Doctrine\Common\Annotations\SimpleAnnotationReader;

class Metadriver extends AbstractService
{
	private $isVoid = true;
	private $data;
	private $filename;
	private $annotReader;

	/**
	 * @return boolean
	 */
	public static function isSingleton()
	{
		return true;
	}

	/**
	 * @return string
	 */
	public static function geExtendedClassNameByBase($baseClass)
	{
		$extended_class_name = '\\' . $this->getApplication()->getNamespace() .
			'\\Extension\\' . $baseClass;
		
		return $extended_class_name;
	}

	/**
	 * @return bool
	 */
	public function isFullyQualifiedName($name)
	{
		return strpos($name, '\\') === 0;
	}
	
	/**
	 * @return null
	 */
	public function init()
	{
		parent::init();
		
		$this->annotReader = new SimpleAnnotationReader();
		$this->annotReader->addNamespace('PHPCrystal\PHPCrystal\Annotation\Action');
		$this->annotReader->addNamespace('PHPCrystal\PHPCrystal\Annotation\Common');

		$this->filename = FileHelper::create('@cache', 'furball.ser');
		$data = $this->filename->unserialize();

		if ($data !== null) {
			$this->isVoid = false;
			$this->data = $data;
		} else {
			$this->flush();
			$this->isVoid = true;
		}
		
		$this->isInitialized = true;
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
				$refClass->isAbstract())
			{
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
	 * @return array
	 */
	public function getClassAnnotations($className)
	{
		$refClass = new \ReflectionClass($className);
		
		$annots = $this->annotReader->getClassAnnotations($refClass);
		
		return $annots;
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
		
		if ( ! $composer_lock->fileExists()) {
			return;
		}
		
		$composer_json = $composer_lock->readJson();
		foreach ($composer_json['packages'] as $pkgInfo) {
			$pkgName = $pkgInfo['name'];
			$pkgBootstrap = FileHelper::create('@app/vendor/', $pkgName, 'bootstrap.php');
			if ( ! $pkgBootstrap->fileExists()) {
				continue;
			}
			
			$pkg_instance = $pkgBootstrap->_require();
			if ( ! $pkg_instance instanceof AbstractExtension ||
				$pkg_instance->getDisableAutoloadFlag())
			{
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
		
		if ( ! isset($this->data[$category][$key])) {
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
