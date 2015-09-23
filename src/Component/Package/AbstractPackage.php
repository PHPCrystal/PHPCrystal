<?php
namespace PHPCrystal\PHPCrystal\Component\Package;

use PHPCrystal\PHPCrystal\Component\Package\Option\Container;
use PHPCrystal\PHPCrystal\Component\Facade\AbstractFacade;
use PHPCrystal\PHPCrystal\Component\Exception as Exception;
use PHPCrystal\PHPCrystal\Component\Factory as Factory;
use PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper;
use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Component\Package\Config;
use PHPCrystal\PHPCrystal\Facade\Metadriver;
use PHPCrystal\PHPCrystal\_Trait\PkgConfigAware;

abstract class AbstractPackage extends Event\AbstractNode
{
	use PkgConfigAware;

	private $builder;
	private $router;
	private $dirname;
	private $priority = null;	
	private $namespace;
	private $composerName;
	private $key;
	private $pkgName;
	
	private $routers = [];

	/**
	 * @var \Composer\Autoload\ClassLoader
	 */
	protected static $autoloader;

	/**
	 * Loads package configuration file
	 * 
	 * @return void
	 */
	final protected function loadConfigFile()
	{
		$context = $this->getApplication()
			->getCurrentEvent()
			->createContext();

		$this->config = FileHelper::create($this->getDirectory(), 'config.php')
			->requireIfExists($context);
	}

	protected function initRouting()
	{
		foreach ($this->getRouters() as $router) {
			$router->init();
		}
	}

	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct();	
		// set package name and namespace
		$parts = explode('\\', get_class($this));		
		$pkgName = strtolower($parts[0] . '/' . $parts[1]);		
		$this->setComposerName($pkgName);
		$this->setNamespace($parts[0] . '\\' . $parts[1]);
		// unique key. being used when dumping package's assets
		$this->key = sha1(get_class($this));
		// every package has its own factory. extensions created by facade will
		// inherit application's factory.
		$this->setFactory(new Factory\Factory($this));
		// not all packages are being created by the facade so we have to assign
		// application instance explicitly
		$this->setApplication(AbstractFacade::getApplication());
		// set default package services
		$this->setBuilder('\\PHPCrystal\\PHPCrystal\\Service\\PackageBuilder\\PackageBuilder');
		
		// assign event listeners
		$this->addEventListener(Event\Type\System\Build::toType(), function($event) {
			$this->onBuildEvent($event);
		});

		$this->addEventListener(Event\Type\System\PkgNotification::toType(), function($event) {
			switch ($event->getNotifWord()) {
				case 'load-config-file':
					$this->loadConfigFile();
					break;
				
				case 'init-routing':
					$this->initRouting();
					break;
			}
		});		
	}

	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Package\Option\Container
	 */
	final public function getConfig()
	{
		return $this->config;
	}

	public function addRouter($className)
	{
		$this->routers[] = $this->getFactory()
			->create($className);
	
		return $this;		
	}

	public function getRouters()
	{
		return $this->routers;
	}
	
	public function getActiveRouter()
	{
		foreach ($this->getRouters() as $router) {
			if ($router->isActive()) {
				return $router;
			}
		}

		Exception\System\FrameworkRuntimeError::create('No active router')
			->_throw();
	}

	/**
	 * @return void
	 */
	public function init()
	{

	}

	/**
	 * @return string
	 */
	final public function getComposerName($dotNotation = false)
	{
		if ($dotNotation) {
			$parts = explode('/', $this->composerName);
			
			return $parts[0] . '.' . $parts[1];
		} else {
			return $this->composerName;
		}
	}

	/**
	 * @return $this
	 */
	final public function setComposerName($name)
	{
		$this->composerName = $name;
		
		return $this;
	}

	/**
	 * @return string
	 */
	final public function getNamespace()
	{
		$refClass = new \ReflectionClass($this);
		
		return $refClass->getNamespaceName();
	}
	
	/**
	 * @return $this
	 */
	final public function setNamespace($pkgNamespace)
	{
		$this->namespace = $pkgNamespace;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	final public function getKey()
	{
		return $this->key;
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Service\Router\AbstractRouter
	 */
	final public function getRouter()
	{
		return $this->router;
	}
	
	/**
	 * @return boolean
	 */
	final public function isApplication()
	{
		return $this === $this->getApplication() ? true : false;
	}

	/**
	 * @return $this
	 */
	final public function setRouter($routerClass)
	{		
		$this->router = $this->getFactory()
			->create($routerClass);
	
		return $this;
	}

	/**
	 * @return PHPCrystal\PHPCrystal\Service\PackageBuilder
	 */
	final public function getBuilder()
	{
		return $this->builder;
	}
	
	/**
	 * @return $this
	 */
	public function setBuilder($className)
	{
		$this->builder = $this->getFactory()
			->create($className);
		
		return $this;
	}

	/**
	 * @return \Composer\Autoload\ClassLoader
	 */
	final public static function getAutoloader()
	{
		return static::$autoloader;
	}
	
	/**
	 * @return string
	 */
	final public function getClassPath($class)
	{
		return dirname(self::getAutoloader()->FindFile($class));
	}
	
	/**
	 * @return string
	 */
	final public function getDirectory()
	{
		return $this->dirname;
	}
	
	/**
	 * @return $this
	 */
	public function setDirectory($pathname)
	{
		$this->dirname = $pathname;
		
		return $this;
	}

	protected function getDefaultBuildEvent()
	{
		return BuildEvent::createDefault();
	}
	
	/**
	 * @return integer
	 */
	final public function getPriority()
	{
		return $this->priority;
	}
	
	/**
	 * @return $this
	 */
	final public function setPriority($value)
	{
		$this->priority = $value;
		
		return $this;
	}
	
	/**
	 * @return PHPCrystal\PHPCrystal\Service\Event\Type\NewInstance
	 */
	public function dispatchNewInstanceEvent($newInstance)
	{
		$newInstanceEvent = Event\Type\System\NewInstance::create($newInstance);
		
		return parent::dispatch($newInstanceEvent);
	}

	/**
	 * @return PHPCrystal\PHPCrystal\Service\Event\Type\InitService
	 */
	public function dispatchInitServiceEvent($serviceInstance)
	{
		$initServiceEvent = Event\Type\System\InitService::create($serviceInstance);

		return parent::dispatch($initServiceEvent);
	}
	
	public function getActions()
	{
		return Metadriver::getPackageActions($this);
	}

	//
	// Event hooks
	//
	
	protected function onServiceInit($event)
	{
		//if ($event->getShortClassName() == 'Cache') {
			echo 3;
		//}
	}
	
	/**
	 * @return null
	 */
	protected function onBuildEvent($event)
	{
		$builder = $this->getBuilder();
		if ( ! $builder) {
			return;
		}
		$builder->run();
	}
}
