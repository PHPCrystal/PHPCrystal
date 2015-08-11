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
	private $port = 80;
	private $userAgent;
	private $remoteAddr;
	private $remotePort;
	private $httpReferer;

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
		$this->uriInput = self::createURIInputFromArray([]);
	}

	/**
	 * @return Inut
	 */
	private static function createGetInputFromArray(array $items)
	{
		return Input::create('GetInput', $items);
	}

	/**
	 * @return Input
	 */
	private static function createPostInputFromArray(array $items)
	{
		return Input::create('PostInput', $items);
	}

	/**
	 * @return Input
	 */
	private static function createCookieInputFromArray(array $items)
	{
		return Input::create('CookieInput', $items);
	}

	/**
	 * @return Input
	 */
	private static function createURIInputFromArray(array $items)
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
	 * @return $this
	 */
	public function setGetInput(Input $input)
	{
		$this->getInput = $input;
		
		return $this;
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
	 * @return $this
	 */
	public function setCookieInput(Input $input)
	{
		$this->cookieInput = $input;
		
		return $this;
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
		$request
			->setServerName($serverName)
			->setPort($_SERVER['SERVER_PORT'])
			->setUserAgent($_SERVER['HTTP_USER_AGENT'])
			->setRemoteAddr($_SERVER['REMOTE_ADDR'])
			->setRemotePort($_SERVER['REMOTE_PORT'])
			->setMethod($_SERVER['REQUEST_METHOD'])
			->setUri(Uri::create($uriString))
			->setHttpReferer(@$_SERVER['HTTP_REFERER'])
			->setGetInput(self::createGetInputFromArray($_GET))
			->setCookieInput(self::createCookieInputFromArray($_COOKIE))
			->setPostInput(self::createPostInputFromArray($_POST))
		;

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
		$request->setCookieInput(self::createCookieInputFromArray($cookies));
		
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
	 * @return $this
	 */
	public function setServerName($serverName)
	{
		$this->serverName = $serverName;
		
		return $this;
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
	final public function getPort()
	{
		return $this->port;
	}
	
	/**
	 * @return $this
	 */
	public function setPort($portNumber)
	{
		$this->port = $portNumber;
		
		return $this;
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
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getRemoteAddr()
	{
		return $this->remoteAddr;
	}
	
	/**
	 * @return $this
	 */
	public function setRemoteAddr($ipAddr)
	{
		$this->remoteAddr = $ipAddr;
		
		return $this;
	}
	
	public function getRemotePort()
	{
		$this->remotePort;
	}
	
	/**
	 * @return $this
	 */
	public function setRemotePort($portNum)
	{
		$this->remotePort = $portNum;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getUserAgent()
	{
		return $this->userAgent;
	}
	
	/**
	 * @return $this
	 */
	public function setUserAgent($userAgent)
	{
		$this->userAgent = $userAgent;
		
		return $this;
	}
	
	public function getHttpReferer()
	{
		return $this->httpReferer;
	}
	
	/**
	 * @return $this
	 */
	public function setHttpReferer($httpReferer)
	{
		$this->httpReferer = $httpReferer;
		
		return $this;
	}
}
