<?php
namespace PHPCrystal\PHPCrystal\Component\Package\Option;

use PHPCrystal\PHPCrystal\Component\Container\AbstractContainer;
use PHPCrystal\PHPCrystal\Component\Filesystem\PathResolver;

class Container extends AbstractContainer
{
	private $secKeyPrefix = array();
	protected static $itemClass = __NAMESPACE__ . '\\Option';

	public function __construct($name = null, $itemsArray = array())
	{
		parent::__construct($name, $itemsArray);
	}
	
	/**
	 * @return $this
	 */
	public function addPathAlias($alias, $pathname, $allowOverride = true)
	{
		PathResolver::addAlias($alias, $pathname, $allowOverride);

		return $this;
	}
	
	public function openSection($keyPrefix)
	{
		if (empty($this->secKeyPrefix)) {
			$this->secKeyPrefix = [];
		}
		$this->secKeyPrefix[] = $keyPrefix;
	}
	
	public function closeSection()
	{
		array_pop($this->secKeyPrefix);// = null;
	}
	
	public function closeAll()
	{
		$this->secKeyPrefix = [];
	}
	
	public function set($key, $value)
	{
		$itemKey = empty($this->secKeyPrefix) ?
			$key : (join('.', $this->secKeyPrefix) . '.' . $key);
		
		return parent::set($itemKey, $value);
	}
}
