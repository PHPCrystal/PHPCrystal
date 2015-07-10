<?php
namespace PHPCrystal\PHPCrystal\Service\Session;

use PHPCrystal\PHPCrystal\Component\Container\AbstractContainer;
use const PHPCrystal\PHPCrystal\Component\Container\ITEM_OPERATION_ADD;
use const PHPCrystal\PHPCrystal\Component\Container\ITEM_OPERATION_NEW_VALUE;

class FlashContainer extends AbstractContainer
{
	protected static $itemClass = 'PHPCrystal\PHPCrystal\Service\Session\Item';
	
	/**
	 * @return array
	 */
	public function toArray()
	{
		$tmp = new Container(null,  []);		
		
		foreach ($this->changesTracker as $itemKey => $opType) {
			if ($opType == ITEM_OPERATION_ADD || $opType == ITEM_OPERATION_NEW_VALUE) {
				$tmp->set($this->get($itemKey));
			}
		}

		return $tmp->toArray();
	}
}
