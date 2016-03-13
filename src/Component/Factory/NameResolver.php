<?php

namespace PHPCrystal\PHPCrystal\Component\Factory;

use Zend\Uri\Uri,
	PHPCrystal\PHPCrystal\Component\Service\AbstractService,
	PHPCrystal\PHPCrystal\Component\MVC as MVC,
	PHPCrystal\PHPCrystal\_Trait\FactoryAware;

class NameResolver
{

	use FactoryAware;

	const ACTION_SCHEME = 'action',
		CONTROLLER_SCHEME = 'ctrl',
		FRONT_CONTROLLER_SCHEME = 'fc',
		SERVICE_SCHEME = 'service';

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
			$URI = new Uri($name);
			$this->normalize_URI($URI);
			$resolved = $this->getFactory()
				->getMetaDriver()
				->getClassNameBy_URI($URI->toString());
		} else {
			return $resolved = $name;
		}

		return $resolved;
	}
	
	/**
	 * @return string
	 */
	public function classNameTo_URI($className)
	{
		if (AbstractService::isSubclass($className)) {
			return $this->toService_URI($className);			
		} else if (MVC\Controller\Action\AbstractAction::isSubclass($className)) {
			return $this->toAction_URI($className);	
		} else if (MVC\Controller\AbstractController::isSubclass($className)) {
			return $this->toController_URI($className);				
		} else if (MVC\Controller\AbstractFrontController::isSubclass($className)) {
			return $this->toFrontController_URI($className);			
		} else {
			return null; 
		}
	}

	/**
	 * @return string
	 */
	public function toAction_URI($className)
	{
		return $this->composeAction_URI($this->parseClassName($className));
	}

	/**
	 * @return string
	 */
	public function toController_URI($className)
	{
		return $this->composeController_URI($this->parseClassName($className));
	}

	/**
	 * @return string
	 */
	public function toFrontController_URI($className)
	{
		return $this->composeController_URI($this->parseClassName($className));
	}

	/**
	 * @return string
	 */
	public function toService_URI($className)
	{
		return $this->composeService_URI($this->parseClassName($className));
	}

	/**
	 * @return array
	 */
	public function getActionRelatedResourceNames($action_URI)
	{
		$metaAction = $this->factory
			->getMetaDriver()
			->findMetaClassBy_URI($action_URI);
		$parsed = $this->parseClassName($metaAction->getBaseClass());

		return [
			self::FRONT_CONTROLLER_SCHEME => $this->composeFrontController_URI($parsed),
			self::CONTROLLER_SCHEME => $this->composeController_URI($parsed)
		];
	}

	/**
	 * @return array
	 */
	private function parseClassName($className)
	{
		$parts = array_map(function($item) {
			return strtolower($item);
		}, explode('\\', ltrim($className, '\\')));

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
		} else if (preg_match('/^[^\\\\]+\\\\[^\\\\]+\\\\FrontController/', $className)) {
			// <Vendor>\<Package>\FrontController\<FrontControllerName>
			$data['fc'] = $parts[3];
		}

		return $data;
	}

	/**
	 * @return string
	 */
	private function composeAction_URI(array $parsed)
	{
		return self::ACTION_SCHEME . '://' . $parsed['package'] . '.' .
			$parsed['vendor'] . '/' . $parsed['fc'] . '/' .
			$parsed['controller'] . '/' . $parsed['action'];
	}

	/**
	 * @return string
	 */
	private function composeController_URI(array $parsed)
	{
		return self::CONTROLLER_SCHEME . '://' . $parsed['package'] . '.' . $parsed['vendor'] .
			'/' . $parsed['fc'] . '/' . $parsed['controller'];
	}

	/**
	 * @return string
	 */
	private function composeFrontController_URI(array $parsed)
	{
		return self::FRONT_CONTROLLER_SCHEME . '://' . $parsed['package'] . '.'
			. $parsed['vendor'] . '/' . $parsed['fc'];
	}

	/**
	 * @return string
	 */
	private function composeService_URI(array $parsed)
	{
		return self::SERVICE_SCHEME . '://' . $parsed['package'] . '.' .
			$parsed['vendor'] . '/' . $parsed['service'];
	}

	/**
	 * @return void
	 */
	private function normalize_URI($URI)
	{
		if (!$URI->getHost()) {
			$URI->setHost($this->getPackage()->getFullName());
		}
	}
}
