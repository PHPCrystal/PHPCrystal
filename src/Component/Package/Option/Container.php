<?php
namespace PHPCrystal\PHPCrystal\Component\Package\Option;

use PHPCrystal\PHPCrystal\Component\Container\AbstractContainer;
use PHPCrystal\PHPCrystal\Component\Filesystem\PathResolver;
use PHPCrystal\PHPCrystal\Component\Service\AbstractSubcontractor;

const SECTION_TYPE_GLOBAL = 1;
const SECTION_TYPE_UNDEFINED = 2;
const SECTION_TYPE_SERVICE = 3;

class Container extends AbstractContainer
{
	private $sectionDef = array();
	private $isServiceSection  = false;
	private $currentSectionType;
	protected static $itemClass = __NAMESPACE__ . '\\Option';
	
	/**
	 * @return string
	 */
	private function getCurrentSectionName()
	{
		$name = '';
		
		foreach ($this->sectionDef as $segment) {
			$name .= $segment['name'] . '.';
		}		
		
		return rtrim($name, '.');
	}
	
	/**
	 * @return string
	 */
	private function getItemKey($keySegment)
	{
		$key = '';
		
		foreach ($this->sectionDef as $segment) {
			$key .= $segment['name'] . '.';
		}		
		
		return $key . $keySegment;
	}

	/**
	 * 
	 */
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

	/**
	 * @return void
	 */
	public function openSection($name, $type = null)
	{
		$this->sectionDef[] = ['name' => $name, 'type' => $type];
	}

	/**
	 * @return void
	 */
	public function serviceSection($serviceName)
	{
		$this->openSection($serviceName, SECTION_TYPE_SERVICE);
		$this->currentSectionType = SECTION_TYPE_SERVICE;
	}
	
	/**
	 * @return void
	 */
	public function closeSection()
	{
		array_pop($this->sectionDef);
		$sectionCount = count($this->sectionDef);
		if ($sectionCount) {
			$this->currentSectionType = $this->sectionDef[$sectionCount - 1]['type'];
		} else {
			$this->currentSectionType = SECTION_TYPE_GLOBAL;
		}
	}

	/**
	 * @return void
	 */
	public function closeAll()
	{
		$this->sectionDef = [];
		$this->currentSectionType = SECTION_TYPE_GLOBAL;
	}
	

	public function set($keySegment, $value)
	{
		$itemKey = $this->getItemKey($keySegment);
		
		if ($value instanceof AbstractSubcontractor &&
			$this->currentSectionType = SECTION_TYPE_SERVICE)
		{
			list($vendor, $contractor) = explode('.', $this->getCurrentSectionName());
			$value->setContractorVendor($vendor);
			$value->setContractorName($contractor);
		}	
		
		return parent::set($itemKey, $value);
	}
}
