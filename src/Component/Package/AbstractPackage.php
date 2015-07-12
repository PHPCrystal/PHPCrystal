<?php
namespace PHPCrystal\PHPCrystal\Component\Package;

use PHPCrystal\PHPCrystal\Component\Facade\AbstractFacade;
use PHPCrystal\PHPCrystal\Component\Exception as Exception;
use PHPCrystal\PHPCrystal\Component\Factory as Factory;
use PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper;
use PHPCrystal\PHPCrystal\Service\Event as Event;

abstract class AbstractPackage extends Event\AbstractNode
{	
	private $builder;
	private $router;
	private $dirname;
	private $priority = null;	
	private $namespace;
	private $composerName;
	private $key;

	/**
	 * @var \Composer\Autoload\ClassLoader
	 */
	protected static $autoloader;
	
	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
		// set package root directory
		if ( ! $this->getDirectory()) {
			$this->setDirectory(realpath($this->getClassPath(get_class($this)) . '/..'));
		}		
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
		$this->setRouter('\\PHPCrystal\\PHPCrystal\\Service\\Router\\_Default');	
		$this->setBuilder('\\PHPCrystal\\PHPCrystal\\Service\\PackageBuilder\\_Default');
		// assign event listeners
		$this->addEventListener(Event\Type\System\Build::toType(), function($event) {
			$this->onBuildEvent($event);
		});
		//$this->addEventListener(Event\Type\System\InitService::toType(), function($event) {
			//return $this->onServiceInit($event);
		//});		
	}
	
	/**
	 * @return void
	 */
	public function init() { }
	
	/**
	 * @return string
	 */
	final public function getComposerName()
	{
		return $this->composerName;
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
	final public function setDirectory($pathname)
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
	
	public static function install()
	{
		echo 1;
	}
}
