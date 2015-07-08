<?php
namespace PHPCrystal\PHPCrystal\Service\Metadriver;

use PHPCrystal\PHPCrystal\Component\Filesystem\PathResolver;
use PHPCrystal\PHPCrystal\Component\Service\AbstractService;
use PHPCrystal\PHPCrystal\Component\Package\AbstractExtension;

class Metadriver extends AbstractService
{
	private $isVoid = true;
	private $data;
	private $filename;

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
		$extendedName = '\\' . $this->getApplication()->getNamespace() .
			'\\Extension\\' . $baseClass;
		
		return $extendedName;
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
		$composerLock = PathResolver::create('@app/composer.lock');
		
		if ( ! $composerLock->fileExists()) {
			return;
		}
		
		$composerJson = $composerLock->readJson();
		foreach ($composerJson['packages'] as $pkgInfo) {
			$pkgName = $pkgInfo['name'];
			$pkgBootstrap = PathResolver::create('@app/vendor/', $pkgName, 'bootstrap.php');
			if ( ! $pkgBootstrap->fileExists()) {
				continue;
			}
			
			$pkgInstance = $pkgBootstrap->_require();
			if ( ! $pkgInstance instanceof AbstractExtension) {
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
	public function getExportedServices()
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
	
	/**
	 * @return null
	 */
	public function init()
	{
		parent::init();
		
		$this->filename = PathResolver::create('@cache', 'furball.ser');
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
