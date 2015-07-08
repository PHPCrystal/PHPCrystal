<?php
namespace PHPCrystal\PHPCrystal\Component\Exception;

use PHPCrystal\PHPCrystal\Component\Factory as Factory;
use PHPCrystal\PHPCrystal\Component\Package\AbstractPackage;

abstract class AbstractException extends \Exception implements
	Factory\Aware\PackageInterface
{
	private $package;
	private $params = array();
	
	/**
	 * @return void
	 */
	public function __debugInfo()
	{
		echo 'Exception message: ' . $this->getMessage() . PHP_EOL;
	}
	
	public function __construct($message, $code = 0, \Exception $previous = null)
    {
		parent::__construct($message, $code,  $previous);
    }
	
	/**
	 * @return $this
	 */
	public static function create($formatStr, $errCode = null, ...$params)
	{
		$errMsg = sprintf($formatStr, ...$params);
		$instance = new static($errMsg, $errCode);		
		// retrieve package instance from which the given exception originates
		$backtrace = debug_backtrace();
		while (count($backtrace) > 0) {
			$entry = array_shift($backtrace);
			if ( ! isset($entry['object'])) {
				continue;
			}
			$object = $entry['object'];
			if ($object instanceof AbstractPackage) {
				$instance->setPackage($object);
				break;
			}
		}

		return $instance;
	}

	/**
	 * @return \PHPCrystal\PHPCrystal\Component\Package\AbstractPackage
	 */
	final public function getPackage()
	{
		return $this->package;
	}
	
	/**
	 * @return void
	 */
	final public function setPackage($package)
	{
		$this->package = $package;
	}

	/**
	 * @return $this
	 */
	final public function addParam($excepParam)
	{
		$this->params[] = $excepParam;
		
		return $this;
	}

	/**
	 * @return void
	 */
	public function _throw()
	{
		throw $this;
	}
}
