<?php
namespace PHPCrystal\PHPCrystal\Component\Container;

use PHPCrystal\PHPCrystal\_Trait\CreateObject;
use PHPCrystal\PHPCrystal\Component\Exception as Exception;

const ITEM_OPERATION_ADD = 1;
const ITEM_OPERATION_REMOVE = 2;
const ITEM_OPERATION_NEW_VALUE = 3;

abstract class AbstractContainer
{
	private $name;
	protected $changesTracker = [];
	protected static $itemClass;
	protected $items = array();
	protected $nestedContainers = array();
	/**
	 * @var boolean
	 */
	protected $allowOverride = true;

	public function __construct($name = null, array $items)
	{
		$this->name = $name;
		$this->items = $this->convertArray($items);
	}
	
	/**
	 * @return string
	 */
	final public function getName()
	{
		return $this->name;
	}
	
	/**
	 * @return array
	 */
	final public function getItems()
	{
		return $this->items;
	}
	
	/**
	 * @return mixed
	 */
	public function get($itemKey, $defaultValue = null, $autoExpand = true)
	{
		$parts = explode('.', $itemKey);
		$arrRef = &$this->items;
		while (count($parts) > 1) {
			$segment = array_shift($parts);
			if ( ! array_key_exists($segment, $arrRef) ||
				! is_array($arrRef[$segment]))
			{
				if ($defaultValue) {
					$this->set($itemKey, $defaultValue);
					return $defaultValue;
				} else {
					return null;
				}
			} else {
				$arrRef = &$arrRef[$segment];
			}
		}
		
		$lastKey = end($parts);		
		if ( ! isset($arrRef[$lastKey])) {
			return null;
		}
		
		$item = $arrRef[$lastKey];
		
		if ( ! $autoExpand) {
			return $item;
		}
		
		if ($item instanceof AbstractItem) {
			return $item->getValue();			
		} else {
			return $item;
		}
	}

	/**
	 * Set an item to a given value using dot notation
	 * 
	 * @return $this
	 */
	public function set($itemKey, $value)
	{
		$parts = explode('.', $itemKey);
		$arrRef = &$this->items;
		while (count($parts) > 1) {
			$segment = array_shift($parts);
			if ( ! array_key_exists($segment, $arrRef) ||
				! is_array($arrRef[$segment]))
			{
				$arrRef[$segment] = array();
			}
			$arrRef = &$arrRef[$segment];			
		}
		
		$lastKey = end($parts);
		$itemClass = static::$itemClass;
		
		if ( ! array_key_exists($lastKey, $arrRef)) {
			$this->changesTracker[$itemKey] = ITEM_OPERATION_ADD;
		} else {
			$this->changesTracker[$itemKey] = ITEM_OPERATION_NEW_VALUE;
		}

		if (is_array($value)) {
			$arrRef[$lastKey] = array();
		} else {
			if ($value instanceof AbstractItem) {
				$arrRef[$lastKey] = $value;				
			} else {
				$newItem = new $itemClass($lastKey, $value);
				$arrRef[$lastKey] = $newItem;
				// if value being set is an object return it so that its method
				// chaining may be achieved
				if (is_object($value)) {
					return $value;
				}
			}
		}
	}
	
	/**
	 * Returns true if item with the given key exists
	 * 
	 * @return boolean
	 */
	final public function has($itemKey)
	{
		$parts = explode('.', $itemKey);
		$arrRef = &$this->items;
		while (count($parts) > 1) {
			$segment = array_shift($parts);
			if ( ! array_key_exists($segment, $arrRef)) {
				return false;
			} else {
				$arrRef = &$arrRef[$segment];				
			}
		}

		$lastKey = end($parts);
		return array_key_exists($lastKey, $arrRef);
	}

	/**
	 * Asserts that item value is set to true
	 * 
	 * @return bool
	 */
	final public function assertTrue($itemKey)
	{
		return $this->get($itemKey) === true;
	}

	/**
	 * Asserts that item value is set to false
	 * 
	 * @return bool
	 */
	final public function assertFalse($itemKey)
	{
		return $this->get($itemKey) === false;
	}

	/**
	 * @return bool
	 */
	final public function hasChanges()
	{
		return count($this->changesTracker) > 0;
	}
	
	/**
	 * @return void
	 */
	final public function flush()
	{
		$this->changesTracker = [];
		foreach ($this->items as $key) {
			$this->changesTracker[$key] = ITEM_OPERATION_REMOVE;
		}
		$this->items = [];
	}

	/**
	 * @return boolean
	 */
	final public function isItemObject($itemKey)
	{
		$mixed = $this->get($itemKey, null, false);
		if ($mixed instanceof AbstractItem) {
			$itemValue = $mixed->getValue();
		}
		
		return is_object($itemValue) ? true : false;
	}

	/**
	 * @return array
	 */
	private function convertArray($arr)
	{
		$result = array();		
		$itemClass = static::$itemClass;
		
		foreach ($arr as $key => $value) {
			if (is_array($value)) {
				$result[$key] = $this->convertArray($value);
			} else if ($value instanceof AbstractItem) {
				$result[$key] = $value;
			} else {
				$result[$key] = new $itemClass($key, $value);
			}
		}
		
		return $result;
	}

	/**
	 * @return $this
	 */
	public static function create($name, array $items = null)
	{
		$items = (array)$items;

		return new static($name, $items);
	}
	
	/**
	 * @return void
	 */
	final public function addItems($itemsArray)
	{
		$this->items = array_merge($this>items, $this->convertArray($itemsArray));
	}

	/**
	 * @return $this
	 */
	public function setContainer($name, $itemsArr = array())
	{		
		if ( ! empty($itemsArr)) {
			$nestedContainer = static::create($name, $itemsArr);
		} else {
			$nestedContainer = new static($name);
		}
		
		$this->nestedContainers[$name] = $nestedContainer;
				
		return $nestedContainer;
	}
	
	/**
	 * @return $this
	 */
	public function getContainer($name)
	{
		if ( ! isset($this->nestedContainers[$name])) {
			throw new \RuntimeException(sprintf('Could not found container "%s"',
				$name));
		}
		
		return $this->nestedContainers[$name];
	}

	/**
	 * @return array
	 */
	private function toArrayHelper($itemsArray)
	{
		$result = array();
		
		foreach ($itemsArray as $itemName => $item) {
			if (is_array($item)) {
				$result[$itemName] = $this->toArrayHelper($item);
			} else {
				$result[$itemName] = $item->getValue();
			}
		}
		
		return $result;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return $this->toArrayHelper($this->items);
	}

	/**
	 * @return integer
	 */
	final public function getCount()
	{
		return count($this->items);
	}
	
	/**
	 * @return boolean
	 */
	final public function isEmpty()
	{
		return $this->getCount() == 0 ? true : false;
	}
	
	/**
	 * @return void
	 */
	private function getAllKeysHelper($keyPrefix, $arr, &$result)
	{
		foreach ($arr as $key => $value) {
			$itemKey = empty($keyPrefix) ? $key : ($keyPrefix . '.' . $key);  
			if (is_array($value)) {
				$this->getAllKeysHelper($itemKey, $value, $result);
			} else {
				$result[] = $itemKey;
			}
		}
	}
	
	/**
	 * @return array
	 */
	public function getAllKeys()
	{
		$result = array();		
		
		$this->getAllKeysHelper('', $this->items, $result);
		
		return $result;
	}
	
	/**
	 * @return $this
	 */
	public function merge($container)
	{
		if (null == $container) {
			return $this;
		}
		
		foreach ($container->getAllKeys() as $itemKey) {
			$this->set($itemKey, $container->get($itemKey));
		}
		
		foreach ($container->nestedContainers as $key => $container) {
			$this->nestedContainers[$key]->merge($container);
		}
		
		return $this;
	}
	
	/**
	 * @return $this
	 */
	public function pluck($key, $newContainerName = null)
	{
		$pluckedItem = $this->get($key);
		
		if ($pluckedItem instanceof $this) {
			return $pluckedItem;
		} else if (is_array($pluckedItem)) {
			return static::create($newContainerName, $pluckedItem);
		} else {		
			Exception\System\WrongType::create('array', $pluckedItem)
				->_throw();
		}
	}
}
