<?php
namespace PHPCrystal\PHPCrystal\Component\Filesystem;

class Finder extends \Symfony\Component\Finder\Finder
{
	/**
	 * @return $this
	 */
	public static function findByFileExt($loc, $fileExt)
	{
		$finder = new static();
		
		return $finder->files()->name('*.' . $fileExt)->in($loc);		
	}
	
	/**
	 * @return $this
	 */
	public static function findPhpFiles($loc)
	{
		return self::findByFileExt($loc, 'php');
	}
	
	/**
	 * @return $this
	 */
	public static function findByFilename($filename, $loc)
	{
		return self::create()->files()->name($filename)->in($loc);
	}
}
