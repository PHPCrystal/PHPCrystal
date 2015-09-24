<?php
namespace PHPCrystal\PHPCrystal\Component\Http\Response\Header;

class Cookie extends AbstractField
{
	private $name;
	private $value;
	private $expire;
	private $path = '/';
	private $domain;
	private $secure = false;
	private $httponly = true;

	/**
	 * @return string
	 */
	protected function getKey()
	{
		return spl_object_hash($this);
	}

	public function __construct($name, $value, $expire = 0)
	{
		parent::__construct();
		$this->name = $name;
		$this->value = $value;
		$this->expire = $expire;
	}
	
	/**
	 * @return $this
	 */
	public function setPath($path)
	{
		$this->path = $path;
		
		return $this;
	}
	
	/**
	 * @return $this
	 */
	public function setDomain($domain)
	{
		$this->domain = $domain;
		
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setHttpOnly($httpOnly)
	{
		$this->httponly = $httpOnly;
		
		return $this;
	}

	/**
	 * @return void
	 */
	public function output()
	{
		setcookie($this->name, $this->value, $this->expire, $this->path,
			$this->domain, $this->secure, $this->httponly);
	}
}
