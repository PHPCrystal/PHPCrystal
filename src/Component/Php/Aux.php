<?php
namespace PHPCrystal\PHPCrystal\Component\Php;

class Aux
{
	/**
	 * Check whether the given class implements interface 
	 * 
	 * @param string $className
	 * @param string $interface
	 * 
	 * @return bool
	 */
	public static function implementsInterface($className, $interface)
	{
		return in_array($interface, class_implements($className));
	}

	/**
	 * @return bool
	 */
	public static function isFullyQualifiedName($name)
	{
		return 0 === strpos($name, '\\') ? true : false;
	}
	
	/**
	 * @return bool
	 */
	public static function isQualifiedName($name)
	{
		return strpos($name, '\\') > 0 ? true : false;
	}
	
	/**
	 * @return bool
	 */
	public static function isUnqualifiedName($name)
	{
		return self::isFullyQualifiedName($extendableName) ||
			self::isQualifiedName($extendableName) ? false : true;
	}
	
	/**
	 * @return string
	 */
	public static function getShortName($className)
	{
		return (new \ReflectionClass($className))->getShortName();
	}
	
	/**
	 * @return string
	 */
	public static function getNamespace($className)
	{
		return (new \ReflectionClass($className))->getNamespaceName();
	}
	
	/**
	 * @return bool
	 */
	public static function isInstantiable($className)
	{
		return (new \ReflectionClass($className))->isInstantiable();
	}
	
	/**
	 * @return string|null
	 */
	public static function getInstantiableParentClass($childClass)
	{
		$parentClass = get_parent_class($childClass);

		return self::isInstantiable($parentClass) ? $parentClass : null;
	}
}
