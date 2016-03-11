<?php

namespace PHPCrystal\PHPCrystal\Component\Factory;

use Zend\Uri\Uri,
	PHPCrystal\PHPCrystal\_Trait\FactoryAware,
	PHPCrystal\PHPCrystal\Service\Metadriver\Metadriver;
	

class NameResolver
{
	use FactoryAware;
	
	const ACTION_SCHEME = 'action',
		CONTROLLER_SCHEME = 'ctrl',
		FRONT_CONTROLLER_SCHEME = 'fc',
		SERVICE_SCHEME = 'service';
	
	/** @var Zend\Uri\Uri */
	private $zend_URI;
	
	/** @var string */
	private $appPkgNS;
	
	private $factory;
	
	/** @var array */
	public static $knownSchemes = [self::ACTION_SCHEME, self::CONTROLLER_SCHEME,
		self::FRONT_CONTROLLER_SCHEME, self::SERVICE_SCHEME];
	
	
	public function __construct($factory)
	{
		$this->factory = $factory;
		$this->appPkgNS = $this->factory
			->getApplication()
			->getNamespace();
 	}
	
	public function init()
	{
		// ...
	}
	
	/**
	 * @return string
	 */
	public function resolve($name)
	{
		$resolved = null;

		if (strpos($name, ':/') != false) {
			$uri = new Uri($name);
			if ( ! $uri->getHost()) {
				$uri->setHost($this->getPackage()->getFullName());
			}
			$resolved = $this->getFactory()
				->getMetaDriver()
				->findClassNameBy_URI($uri->toString());
		} else {
			return $resolved = $name;
		}
		
		return $resolved;
	}
	
	/**
	 * @return string
	 */
	public function toAction_URI($className)
	{
		$parsed = $this->parseClassName(ltrim($className, '\\'));
		
		return self::ACTION_SCHEME . '://' . $parsed['package'] . '.' .
			$parsed['vendor'] . '/' . $parsed['fc'] . '/' .
			$parsed['controller'] . '/' . $parsed['action'];
	}
	
	/**
	 * @return string
	 */
	public function toController_URI($className)
	{
		$parsed = $this->parseClassName($className);
		
		return self::CONTROLLER_SCHEME . '://' . $parsed['package'] . '.' . $parsed['vendor'] .
			'/' . $parsed['fc'] . '/' . $parsed['controller'];
	}

	/**
	 * @return string
	 */
	public function toFrontController_URI($className)
	{
		$parsed = $this->parseClassName($className);
		
		return self::FRONT_CONTROLLER_SCHEME . '://' . $parsed['package'] . '.'
			. $parsed['vendor'] . '/' . $parsed['fc'];
	}
	
	/**
	 * @return string
	 */
	public function toService_URI($className)
	{
		$parsed = $this->parseClassName($className);
		
		return self::SERVICE_SCHEME . '://' . $parsed['package'] . '.' .
			$parsed['vendor'] . '/' . $parsed['service'];
	}
	
	/**
	 * @return string
	 */
	public function getName($URI_Str)
	{
		$this->zend_URI = new Uri($URI_Str);
		
		switch ($this->zend_URI->getScheme()) {
			case 'action':
				$names = $this->getActionName();
				break;
			
			case 'ctrl':
				$names = $this->getControllerName();
				break;
			
			case 'fc':
				$names = $this->getFrontControllerName();
				break;
		}
		
		if ($this->getPackage()->isApplication()
			&& ! class_exists($names[1])) {
			return $names[0]; // base class name
		} else {
			return $names[1]; // extended
		}
	}

	/**
	 * @return string
	 */
	private function getPkgNS()
	{
		$host = $this->zend_URI->getHost();
		
		if (empty($host)) {
			return $this->getPackage()->getNamespace();
		} else {
			//return $this->metadriver->getPackageByDotname($host);
		}
	}
	
	/**
	 * @return string
	 */
	private function pathSegmentToNS()
	{
		return str_replace('/', '\\',
			ltrim(rtrim($this->zend_URI->getPath(), '/'), '/'));
	}
	
	/**
	 * @return string
	 */
	private function getActionName()
	{
		$pkgNS = $this->getPkgNS();
		$pathSegmentNS = $this->pathSegmentToNS();
		$base = "\\$pkgNS\\Action\\$pathSegmentNS";		
		$extended = "\\{$this->appPkgNS}\\Ext\\$pkgNS\\Action\\$pathSegmentNS";
		
		return [$base, $extended];
	}
	
	/**
	 * @return string
	 */
	private function getControllerName($URI)
	{
		
	}
	
	/**
	 * @return string
	 */
	private function getFrontControllerName($URI)
	{
		
	}
	
	/**
	 * @return array
	 */
	private function parseClassName($className)
	{
		$parts = array_map(function($item) {
			return strtolower($item);
		}, explode('\\', $className));
		
		$data = [];
		$data['vendor'] = $parts[0];
		$data['package'] = $parts[1];
		
		if (preg_match('/^[^\\\\]+\\\\[^\\\\]+\\\\Service/', $className)) {
			// <Vendor>\<Package>\Service\<ServiceName>
			$data['service'] = join('/', array_slice($parts, 3, -1));
		} else if (preg_match('/^[^\\\\]+\\\\[^\\\\]+\\\\Action/', $className)) {
			// <Vendor>\<Package>\Action\<FrontController>\<Controller>\<ActionName>
			$data['fc'] = $parts[3];
			$data['controller'] = $parts[4];
			$data['action'] = $parts[5];
		} else if (preg_match('/^[^\\\\]+\\\\[^\\\\]+\\\\Controller/', $className)) {
			// <Vendor>\<Package>\Controller\<FrontControllerName>\<ControllerName>
			$data['fc'] = $parts[3];
			$data['controller'] = $parts[4];
		} else if (preg_match('^/[^\\\\]+\\\\[^\\\\]+\\\\FrontController/', $className)) {
			// <Vendor>\<Package>\FrontController\<FrontControllerName>
			$data['fc'] = $parts[3];
		}
		
		return $data;
	}
}
