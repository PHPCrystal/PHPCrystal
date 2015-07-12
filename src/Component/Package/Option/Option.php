<?php
namespace PHPCrystal\PHPCrystal\Component\Package\Option;

use PHPCrystal\PHPCrystal\Component\Container\AbstractItem;
use PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper;

class Option extends AbstractItem
{
	private function convertItemValue($mixed)
	{
		if ($mixed instanceof FileHelper) {
			return $mixed->toString();
		} else if (is_array($mixed)) {
			foreach ($mixed as $arrKey => $arrValue) {
				$mixed[$arrKey] = $this->convertItemValue($arrValue);
			}
		} else {
			return $mixed;
		}
	}
	
	public function getValue()
	{
		$value = parent::getValue();
		
		return $this->convertItemValue($value);
	}
}
