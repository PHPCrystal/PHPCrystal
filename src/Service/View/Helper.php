<?php
namespace PHPCrystal\PHPCrystal\Service\View;

use PHPCrystal\PHPCrystal\Component\Factory as Factory;
use PHPCrystal\PHPCrystal\Component\Http\Uri;
use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Component\Service\AbstractService;

class Helper extends AbstractService
{
	/**
	 * @var \PHPCrystal\PHPCrystal\Component\Http\Uri
	 */
	private $requestUri;
	private $request;
	
	/**
	 * @return array
	 */
	public static function getWakeupEvents()
	{
		return [new Event\Type\Http\Request()];
	}	
	
	/**
	 * @return void
	 */
	public function init()
	{
		$this->request = $this->getApplication()->getCurrentEvent()
			->getRequest();
		$this->requestUri = $this->request->getUri();
	}

	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Http\Uri
	 */
	public function getUrl($actionName, ...$uriParams)
	{
		$action = $this->getFactory()
			->createAction($actionName);
		
		$mixed = $action->getReverseUri(...$uriParams);
		if (is_string($mixed)) {
			$tmpUri = new Uri($mixed);
		} else if ($mixed instanceof Uri) {
			$tmpUri = $mixed;
		}
		
		$resultUri = null;
		
		if ($tmpUri->isValidRelative()) {
			$router = $action->getPackage()->getActiveRouter();
			$resultUri = $tmpUri->merge($router->getBaseUri(), $tmpUri);
		} else {
			$resultUri = $tmpUri;
		}
		
		return $resultUri;
	}
	
	/**
	 * @return string
	 */
	public function getHostname()
	{
		return $this->request->getHostname();
	}
	
	/**
	 * @return string
	 */
	public function getBaseUri()
	{
		return $this->requestUri->makeBaseUri()->toString();
	}
}
