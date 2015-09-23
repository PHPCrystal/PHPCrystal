<?php
namespace PHPCrystal\PHPCrystal\Service\Storage\Persistent\Filesystem;

use PHPCrystal\PHPCrystal\Component\Service\AbstractSubcontractor;
use PHPCrystal\PHPCrystal\Component\Exception as Exception;

class Filesystem extends AbstractSubcontractor
{
	private $storageBasedir;
	private $filenamePrefix = '';
	
	/**
	 * @return string
	 */
	private function getFilenameByKey($key)
	{
		return $this->storageBasedir . DIRECTORY_SEPARATOR .
			$this->filenamePrefix . $key;		
	}
	
	/**
	 * @return void
	 */
	public function init()
	{
		if ($this->getContractorName() == 'phpcrystal.phpcrystal.session') {
			$config = $this->getContractorConfig();
			$this->setStorageBasedir($config->get('save_path'));
			$this->setFilenamePrefix($this->getContractorName() . '_');
		}
	}

	/**
	 * @return string|null
	 */
	public function getStorageBasedir()
	{
		return $this->storageBasedir;
	}
	
	/**
	 * @return $this
	 */
	public function setStorageBasedir($dirname)
	{
		$this->storageBasedir = $dirname;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getFilenamePrefix()
	{
		return $this->filenamePrefix;
	}
	
	/**
	 * @return $this
	 */
	public function setFilenamePrefix($prefix)
	{
		$this->filenamePrefix = $prefix;
	}
	
	/**
	 * @return string|null
	 */
	public function get($key, $default = null)
	{
		$filename = $this->getFilenameByKey($key);

		$fd = fopen($filename, 'r');		
		Exception\System\IO_Filesystem::assertFd($fd, $filename);
		
		$fileContent = fread($fd, filesize($filename));
		fclose($fd);
		
		return unserialize($fileContent);
	}
	
	/**
	 * @return void
	 */
	public function set($key, $value)
	{
		$filename = $this->getFilenameByKey($key);

		$fd = fopen($filename, 'w');		
		Exception\System\IO_Filesystem::assertFd($fd, $filename);

		$data = serialize($value);
		fwrite($fd, $data);
		fclose($fd);
	}
	
	/**
	 * @return bool
	 */
	public function has($key)
	{
		$filename = $this->getFilenameByKey($key);
		
		return file_exists($filename);
	}
}
