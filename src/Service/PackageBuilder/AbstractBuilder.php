<?php

namespace PHPCrystal\PHPCrystal\Service\PackageBuilder;

use PHPCrystal\PHPCrystal\Component\Service\AbstractService,
	PHPCrystal\PHPCrystal\Component\Filesystem\Finder,
	PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper,
	PHPCrystal\PHPCrystal\Component\Php\Parser,
	Doctrine\Common\Annotations\AnnotationReader;


abstract class AbstractBuilder extends AbstractService
{
	protected $annotReader;

	/**
	 * {@inherited}
	 */
	public static function isSingleton()
	{
		return false;
	}
	
	/**
	 * @return void
	 */
	public function init()
	{
		$this->annotReader = new AnnotationReader();
	}	

	/**
	 * @return array
	 */
	protected function scan($targetDir, \Closure $callback)
	{
		$result = array();
		$loc = FileHelper::create($this->getPackage()->getDirectory(), $targetDir);

		if ( ! $loc->dirExists()) {
			return $result;
		}

		$phpFiles = Finder::create()->findPhpFiles($loc->toString());

		foreach ($phpFiles as $file) {
			$className = Parser::loadFromFile($file->getRealpath())
				->parseClass();
			$callbackResult = $callback($className);
			
			if ($callbackResult !== null) {
				$result[] = $callbackResult;
			}
		}

		return $result;
	}

	//
	// Abstract methods
	//

	abstract public function run();
}
