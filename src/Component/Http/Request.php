<?php
namespace PHPCrystal\PHPCrystal\Component\Http;

use PHPCrystal\PHPCrystal\Component\MVC\Controller\Input\Input;
use PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper;
use PHPCrystal\PHPCrystal\Component\Http\Uri;

const REQUEST_INPUT_GET = 1;
const REQUEST_INPUT_POST = 2;
const REQUEST_INPUT_COOKIE = 4;
const REQUEST_INPUT_URI = 8;

class Request extends \Zend\Http\Request
{
	private $serverName;
	private $serverPort = 80;
	private $userAgent;
	private $remoteIpAddr;

	/**
	 * @var \PHPCrystal\PHPCrystal\Component\MVC\Controller\Input\Input
	 */	
	private $getInput;
	
	/**
	 * @var \PHPCrystal\PHPCrystal\Component\MVC\Controller\Input\Input
	 */
	private $postInput;
	
	/**
	 * @var \PHPCrystal\PHPCrystal\Component\MVC\Controller\Input\Input
	 */
	private $cookieInput;
	
	/**
	 * @var \PHPCrystal\PHPCrystal\Component\MVC\Controller\Input\Input
	 */
	private $uriInput;	

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
	 * @api
	 */
	public function __construct()
	{
		$this->uriInput = $this->createURIInput([]);
	}
	
	/**
	 * @return Inut
	 */
	private function createGetInput(array $items)
	{
		return Input::create('GetInput', $items);
	}
	
	/**
	 * @return Input
	 */
	private function createPostInputContainer(array $items)
	{
		return Input::create('PostInput', $items);
	}
	
	/**
	 * @return Input
	 */
	private function createCookieInput(array $items)
	{
		return Input::create('CookieInput', $items);
	}
	
	/**
	 * @return Input
	 */
	private function createURIInput(array $items)
	{
		return Input::create('UriInput', $items);
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
	 * @return \PHPCrystal\PHPCrystal\Component\MVC\Controller\Input\Input
	 */
	final public function getPostInput()
	{
		return $this->postInput;
	}

	/**
	 * @return $this
	 */
	final public function setPostInput(Input $input)
	{
		$this->postInput = $input;
		
		return $this;
	}

	/**
	 * @return \PHPCrystal\PHPCrystal\Component\MVC\Controller\Input\Input
	 */
	final public function getCookieInput()
	{
		return $this->cookieInput;
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Component\MVC\Controller\Input\Input
	 */
	final public function getURIInput()
	{
		return $this->uriInput;
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
		
		$request = new static();
		$request->setMethod($_SERVER['REQUEST_METHOD']);
		$request->setUri(Uri::create($uriString));
		$request->serverName = $serverName;
		$request->serverPort = $_SERVER['SERVER_PORT'];
		
		$request->getInput = $request->createGetInput($_GET);
		$request->cookieInput = $request->createCookieInput($_COOKIE);
		
		if ($request->isPost()) {
			$postInput = $this->createPostInputContainer($_POST);
			$this->setPostInput($postInput);
		}

		return $request;
	}
	
	/**
	 * @return $this
	 */
	public static function createFromFile($filename)
	{
		$requestStr = FileHelper::create($filename)
			->getFileContent();

		$request =  static::createFromString($requestStr);
		if ($request->isPost()) {
			$postRawData = array();
			foreach (explode('&', rawurldecode($request->getContent())) as $dataItem) {
				$keyValue = explode('=', $dataItem);
				$postRawData[$keyValue[0]] = $keyValue[1];
			}
			$request->setPostInput(Input::create('postData', $postRawData));
		}
		
		$cookieHeader = $request->getHeaders()->get('Cookie');
		$cookies = $cookieHeader instanceof \Zend\Http\Header\Cookie ?
			$cookieHeader->getArrayCopy() : [];
		$request->cookieInput = $request->createCookieInput($cookies);
		
		return $request;
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
	
	/**
	 * @return string
	 */
	public function getRemoteIpAddr()
	{
		return $this->remoteIpAddr;
	}
	
	/**
	 * @return string
	 */
	public function getUserAgent()
	{
		return $this->userAgent;
	}
}
