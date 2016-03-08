<?php
namespace PHPCrystal\PHPCrystal\Component\Service;

final class MetaService
{
	/** @var string $key */
	private $key;	
	/** @var string */
	private $className;
	/** @var array Array of interfaces implemented by service */
	private $implements = [];
	/** @var integer */
	private $priority;
	/** @var bool */
	private $isActive = true;

	/**
	 * @param string	$className
	 * @param integer	$priority
	 */
	public function __construct($className, $priority)
	{
		$this->className = $className;
		$this->implements = class_implements($className);
		sort($this->implements);
		$this->key = sha1(join(',', $this->implements));
		$this->priority = $priority;
	}

	/**
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}
	
	/**
	 * @return integer
	 */
	public function getPriority()
	{
		return $this->priority;
	}
	
	/**
	 * @return bool
	 */
	public function getActiveFlag()
	{
		return $this->isActive;
	}

	/**
	 * @return void
	 */
	public function setActiveFlag($flag)
	{
		$this->isActive = $flag;
	}
	
	/**
	 * Returns TRUE if all given interfaces are implemented by the service
	 * 
	 * @return bool
	 */
	public function check($name)
	{
		$names = (array)$name;

		foreach ($names as $search) {
			if ( ! in_array($search, $this->implements)) {
				return false;
			}
		}
		
		return true;
	}
}
