<?php
namespace PHPCrystal\PHPCrystal\Component\Object;

class Object
{
	/**
	 * Returns TRUE if the given class is a sublass of the current class
	 * 
	 * @return bool
	 */
	public static function isSubclass($className)
	{
		return is_subclass_of($className, get_called_class());
	}
	
	/**
	 * @return bool
	 */
	public function is_same_class($object)
	{
		return get_class($this) == get_class($object);
	}
}
