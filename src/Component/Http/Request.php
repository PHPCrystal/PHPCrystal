<?php
namespace PHPCrystal\PHPCrystal\Component\Http;

use PHPCrystal\PHPCrystal\Component\MVC\Controller\Input\Input;
use PHPCrystal\PHPCrystal\Component\Filesystem\PathResolver;
use PHPCrystal\PHPCrystal\Component\Http\Uri;

class Request extends \Zend\Http\Request
{
	private $serverName;
	private $serverPort = 80;
	private $getInput;
	private $postInput;
	
	public function __construct()
	{
		$this->getInput = $this->createGetInputContainer();
		$this->postInput = $this->createPostInputContainer();
	}
	
	/**
	 * @return array
	 */
	public static function getKnownHttpMethods()
	{
		return array(self::METHOD_CONNECT, self::METHOD_DELETE, self::METHOD_GET,
			self::METHOD_HEAD, self::METHOD_OPTIONS, self::METHOD_PATCH, self::METHOD_POST,
			self::METHOD_PROPFIND, self::METHOD_PUT, self::METHOD_TRACE);
	}
	
	/**
	 * @return Inut
	 */
	private function createGetInputContainer($itemsArray = array())
	{
		return Input::create('GetData', $itemsArray);
	}
	
	/**
	 * @return Input
	 */
	private function createPostInputContainer($itemsArray = array())
	{
		return Input::create('postData', $itemsArray);
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Component\MVC\Controller\Input\Input
	 */
	final public function getGetInput()
	{
		return $this->getInput;
	}
	
	/**
	 * @return void
	 */
	final public function setGetInput(Input $getData)
	{
		$this->getInput = $getData;
	}

	/**
	 * @return void
	 */
	final public function getPostInput()
	{
		return $this->postInput;
	}
	
	/**
	 * @return
	 */
	final public function setPostInput(Input $input)
	{
		$this->postInput = $input;
		
		return $this;
	}

	/**
	 * @return $this
	 */
	public static function createFromGlobals()
	{
		$serverName = $_SERVER['SERVER_NAME'];
		$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ||
			$_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';		
		$uriString = $scheme . '://' . $serverName . $_SERVER['REQUEST_URI'];
		
		$reqInstance = new static();
		$reqInstance->setMethod($_SERVER['REQUEST_METHOD']);
		$reqInstance->setUri(Uri::create($uriString));
		$reqInstance->serverName = $serverName;
		$reqInstance->serverPort = $_SERVER['SERVER_PORT'];
		
		if ($reqInstance->isPost()) {
			$postInput = $this->createPostInputContainer($_POST);
			$this->setPostInput($postInput);
		}

		return $reqInstance;
	}
	
	/**
	 * @return $this
	 */
	public static function createFromFile($filename)
	{
		$requestStr = PathResolver::create($filename)
			->getFileContent();

		$reqInstance =  static::createFromString($requestStr);
		if ($reqInstance->isPost()) {
			$postRawData = array();
			foreach (explode('&', rawurldecode($reqInstance->getContent())) as $dataItem) {
				$keyValue = explode('=', $dataItem);
				$postRawData[$keyValue[0]] = $keyValue[1];
			}
			$reqInstance->setPostInput(Input::create('postData', $postRawData));
		}
		
		return $reqInstance;
	}

	/**
	 * @return $this
	 */
	public static function createFromString($requestStr)
	{
		return self::fromString($requestStr);
	}
	
	/**
	 * @return string
	 */
	final public function getServerName()
	{
		return $this->serverName;
	}
	
	/**
	 * @return string
	 */
	final public function getHostname()
	{
		return $this->serverName;
	}
	
	/**
	 * @return integer
	 */
	final public function getServerPort()
	{
		return $this->serverPort;
	}

	/**
	 * @return string|null
	 */
	final public function getContentType()
	{
		return isset($this->httpHeaders['Content-Type']) ?
			$this->httpHeaders['Content-Type'] : null; 
	}
	
	/**
	 * We have to override the parent method because we extend Zend\Http\Uri
	 * class
	 * 
	 * @return void
	 */
	public function setUri($mixed)
	{
		if (is_string($mixed)) {
			$uri = new Uri($mixed);
		} else {
			$uri = $mixed;
		}

		$this->uri = $uri;
	}
}
