<?php

namespace PHPCrystal\PHPCrystal\Service\PackageBuilder;

use PHPCrystal\PHPCrystal\Component\Service\AbstractService,
	PHPCrystal\PHPCrystal\Service\Metadriver\Metadriver,
	PHPCrystal\PHPCrystal\Component\Filesystem\Finder,
	PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper,
	PHPCrystal\PHPCrystal\Component\Php\Parser,
	Doctrine\Common\Annotations\AnnotationReader;


abstract class AbstractBuilder extends AbstractService
{
	protected $annotReader;
	
	/** @var Metadriver */
	protected $metadriver;
	
	/**
	 * 
	 */
	public function __construct(Metadriver $metadriver)
	{
		parent::__construct();
		$this->metadriver = $metadriver;
	}

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
	protected function scanPhpDefinitions($targetDir, \Closure $callback, $exclude = [])
	{
		$result = array();
		$loc = FileHelper::create($this->getPackage()->getDirectory(), $targetDir);

		if ( ! $loc->dirExists()) {
			return $result;
		}

		$phpFiles = Finder::create()
			->findPhpFiles($loc->toString())
			->exclude($exclude);

		foreach ($phpFiles as $file) {
			$className = Parser::loadFromFile($file->getRealpath())
				->parseClass();
			$interface = Parser::loadFromFile($file->getRealpath())
				->parseInterface();			
			$callbackResult = $callback($className, $interface);
			
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
