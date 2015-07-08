<?php
namespace PHPCrystal\PHPCrystal\Service\Metadriver;

class MetaExtension extends AbstractMetaContainer
{
	private $dirname;
	
	/**
	 * @api
	 */
	public function __construct($dirname)
	{
		$this->dirname = $dirname;
	}

	/**
	 * @return string
	 */
	public function getDirectoryName()
	{
		return $this->dirname;
	}
	
	/**
	 * @return void
	 */
	public function setDirectoryName($dirname)
	{
		$this->dirname = $dirname;
	}
}
