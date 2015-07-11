<?php
namespace PHPCrystal\PHPCrystal\Service\Metadriver;

use PHPCrystal\PHPCrystal\Contract\EventCatalyst;

abstract class AbstractMetaContainer
{
	/**
	 * @var array
	 */
	private $annotations = [];
	
	/**
	 * @return array
	 */
	public function getAnnotations()
	{
		return $this->annotations;
	}
	
	/**
	 * @return void
	 */
	public function addAnnotations(array $annots)
	{
		$this->annotations = $annots;
	}
	
	/**
	 * @return array
	 */
	final public function getEventCatalystAnnotations()
	{
		$result = [];

		foreach ($this->getAnnotations() as $annot) {
			if ($annot instanceof EventCatalyst) {
				$result[] = $annot;
			}
		}

		return $result;
	}	
}
