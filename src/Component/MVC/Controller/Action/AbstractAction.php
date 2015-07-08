<?php
namespace PHPCrystal\PHPCrystal\Component\MVC\Controller\Action;

use PHPCrystal\PHPCrystal\Component\Http\Request;
use PHPCrystal\PHPCrystal\Component\Http\Uri;
use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Component\Factory as Factory;

abstract class AbstractAction extends Event\AbstractNode implements
	Factory\InitiableInterface,
	Factory\Aware\DependencyInjectionInterface
{
	private $allowedHttpMethods = array();
	private $controllerMethod;
	private $uriMatchRegExp;
	
	/**
	 * If set to false the action didn't match the request
	 * 
	 * @var boolean
	 */
	private $isValid = true;
	private $status = null;
	/**
	 * @var boolean
	 */
	protected $startTransaction = false;
	protected $transactionLevel = null;
	
	protected $ctrlInstance;
	protected $ctrlInput;
	
	protected $execResult;
	protected $execSuccess = false;
	
	// Router hostname and URI path prefix. Being used by reverse routing
	protected $routerHostname;
	protected $routerUriPathPrefix;

	/**
	 * @return boolean
	 */
	public static function fireEventUponInstantiation()
	{
		return false;
	}
	
	/**
	 * Returns the canonical name of an action
	 * 
	 * @return string
	 */
	final public function getName()
	{
		$parts = explode('\\', get_class($this));
		
		return join('\\', array_slice($parts, 3));
	}
	
	/**
	 * @return boolean
	 */
	final public function isValid()
	{
		return $this->isValid;
	}
	
	/**
	 * @return void
	 */
	final public function setValidityFlag($flag)
	{
		$this->isValid = $flag;
	}
	
	/**
	 * @return void
	 */
	final public function setHostname($hostname)
	{
		$this->routerHostname = $hostname;
	}

	/**
	 * @return null
	 */
	protected function getValidator()
	{
		return null;
	}
	
	/**
	 * @return boolean
	 */
	public function matchRequest(Request $request)
	{
		$allowedMethods = $this->getAllowedHttpMethods();
		if ( ! empty($allowedMethods) && ! in_array($request->getMethod(), $allowedMethods)) {
			return false;
		}
		
		$regExp = $this->getUriMatchRegExp();
		
		if ( ! empty($regExp)) {
			$matches = null;
			if ( ! $request->getUri()->matchUriPath($regExp, $matches)) {
				return false;
			}

			$getContainer = $request->getGetInput();
			array_shift($matches);

			foreach ($matches as $itemKey => $itemValue) {
				if (is_integer($itemKey))  {
					continue;
				}
				$getContainer->set($itemKey, $itemValue);
			}
		
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * @return void
	 */
	public function init()
	{
		$this->addEventListener(Event\Type\Http\Request::toType(), function($event) {
			$this->onHttpRequest($event);
		});
		
		$this->addEventListener(Event\Type\Http\Response200::toType(), function($event) {
			$requestEvent = $event->getLastDispatchedEvent();
			$execResult = $requestEvent->getResult();
			return $this->onResponse200($event, $execResult);
		});
	}

	/**
	 * @return void
	 */
	final public function redirect(Uri $uri, $code = 302)
	{
		switch ($code) {
			case 302:
				$redirectEvent = Event\Type\Http\Response302::create()
					->setLocationUri($uri);
				break;
			
			case 303:
				$redirectEvent = Event\Type\Http\Response303::create()
					->setLocationUri($uri);
				break;
		}
		
		$this->getApplication()->getCurrentEvent()
			->setAutoTriggerEvent($redirectEvent);
	}
	
	/**
	 * @return void
	 */
	final public function redirectToAction($actionName, $urlParams = [], $code = 303)
	{
		$action = $this->getFactory()->createAction($actionName);
		$uri = $action->getReverseUri($urlParams);
		
		$this->redirect($uri, $code);
	}
	
	/**
	 * @return array
	 */
	public function getAllowedHttpMethods()
	{
		return $this->allowedHttpMethods;
	}
	
	/**
	 * @return void
	 */
	final public function setAllowedHttpMethods(array $methods)
	{
		$this->allowedHttpMethods = $methods;
	}
	
	/**
	 * @return string
	 */
	public function getUriMatchRegExp()
	{
		return $this->uriMatchRegExp;
	}
	
	/**
	 * @return void
	 */
	final public function setUriMatchRegExp($regExp)
	{
		$this->uriMatchRegExp = $regExp;
	}
	
	/**
	 * @return string
	 */
	public function getControllerMethod(Request $request = null)
	{
		return $this->controllerMethod;
	}
	
	/**
	 * @return void
	 */
	final public function setControllerMethod($name)
	{
		$this->controllerMethod = $name;
	}

	/**
	 * @return Controller
	 */
	final public function getController()
	{
		return $this->controller;
	}
	
	/**
	 * @return
	 */
	final public function execute($event)
	{
		try {
			$this->onPreExec($event);			
			
			$methodName = $this->getControllerMethod($event->getRequest());				
			$ctrlMethodServices = $this->getFactory()
				->getMethodInjectedServices($this->ctrlInstance, $methodName);
			
			$ctrlArgs = array_merge([$this->getInput()], $ctrlMethodServices);
			$this->execResult = call_user_func_array([$this->ctrlInstance, $methodName],
				$ctrlArgs);

			if ($this->execResult === false) {		
				return $this->onGracefulFail($event);
			}
		} catch (\Exception $e) {
			$this->onHardFail($event, $e);
			throw $e;
		}
	
		// the value returned by this method will be assigned to the request
		// event result.
		return $this->onPostExec($event);
	}

	final public function getInput()
	{
		return $this->ctrlInput;
	}
	
	/**
	 * @return string
	 */
	final public function getReverseUri(...$params)
	{
		if (method_exists($this, 'defineReverseUri')) {
			return $this->defineReverseUri(...$params);
		}
	}
	
	//
	// Event hooks
	//

	/**
	 * @return mixed
	 */
	final protected function onHttpRequest($event)
	{
		if ($event->getPhase() == Event\PHASE_DOWN) {
			// Set controller instance
			$this->ctrlInstance = $event->getTarget()
				->getPropagationPathPrevNode($this);

			// Do data validation if necessary
			$validator = $this->getValidator();
			if ($validator) {
				$result = $validator->run();
				if ( ! $result) {
					$event->discard();
					$this->onDataValidationFail($validator);
				}
			}

		} else if ($event->getPhase() == Event\PHASE_UP && $this->execResult !== false) {
			$this->onSuccess($event);
		}
	}
	
	/**
	 * @return void
	 */
	public function onResponse200($event, $execResult = null)
	{
		
	}
		
	/**
	 * @return void
	 */
	protected function onPreExec($event)
	{
		if ($this->startTransaction) {
			$this->ctrlInstance->getDbAdapter()
				->startTransaction($this->transactionLevel);
		}
	}
	
	/**
	 * @return mixed
	 */
	protected function onPostExec($event)
	{
		if ($this->startTransaction) {
			$this->ctrlInstance->getDbAdapter()->commit();
		}
		
		return $this->execResult;
	}
	
	/**
	 * @return void
	 */
	protected function onSuccess($event) {  }
	
	/**
	 * @return void
	 */
	protected function onGracefulFail($event)
	{
		if ($this->startTransaction) {
			$this->ctrlInstance->getDbAdapter()->rollback();
		}	
	}
	
	/**
	 * @return void
	 */
	protected function onHardFail($event, \Exception $e)
	{
		if ($this->startTransaction) {
			$this->ctrlInstance->getDbAdapter()->rollback();
		}		
	}
	
	/**
	 * @return void
	 */
	protected function onDataValidationFail($event, $validator) {  }
}
