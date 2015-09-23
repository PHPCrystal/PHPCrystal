<?php
namespace PHPCrystal\PHPCrystal\Component\Package\Option;

use PHPCrystal\PHPCrystal\Component\Container\AbstractContainer;
use PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper;
use PHPCrystal\PHPCrystal\Component\Service\AbstractSubcontractor;
use PHPCrystal\PHPCrystal\Component\Facade\AbstractFacade;

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
	 * @return \PHPCrystal\PHPCrystal\Component\Package\AbstractApplication
	 */
	public function getApplication()
	{
		return AbstractFacade::getApplication();
	}	

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
		FileHelper::addAlias($alias, $pathname, $allowOverride);

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
	 * Opens a service configuration section
	 * 
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

	/**
	 * {@inherited}
	 */
	public function set($keySegment, $value)
	{
		$itemKey = $this->getItemKey($keySegment);

		// if value to set is an subcontractor instance we have to let it 'know' that
		// it's needed by a specified contractor so it would do a proper initialization
		if ($value instanceof AbstractSubcontractor &&
			$this->currentSectionType = SECTION_TYPE_SERVICE)
		{
			$value->setContractorName($this->getCurrentSectionName());
		}

		return parent::set($itemKey, $value);
	}
}
