<?php
// Application config container
namespace PHPCrystal\PHPCrystal\Component\Service;

use PHPCrystal\PHPCrystal\Component\Container\AbstractConfig,
	PHPCrystal\PHPCrystal\Service\Metadriver\Metadriver;

class Config extends AbstractConfig
{
	protected $metadriver;
	
	/**
	 * 
	 */
	public function __construct(array $items = [], Metadriver $metadriver)
	{
		parent::__construct($items);
		$this->metadriver = $metadriver;
	}
	
	/**
	 * @return
	 */
	public function service($name)
	{
		$dotName = $this->getPackage()->getDotName() . '.service.' . $name;
	}
	
	/**
	 * 
	 */
	public function set($itemKey, $value)
	{
		if ($value instanceof AbstractSubcontractor) {
			
		}
		parent::set($itemKey, $value);
	}
}
