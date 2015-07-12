<?php
namespace PHPCrystal\PHPCrystal\Component\Filesystem;

use PHPCrystal\PHPCrystal\Component\Exception\System\IO_Filesystem;
use PHPCrystal\PHPCrystal\_Trait\CreateObject;

class FileHelper
{
	use CreateObject;

	/**
	 * @var string
	 */
	private $pathname = '';
	private $fileExt;
	private $fd;
	private $pathParts = array();
	private $resolvedParts;
	private static $aliases = array();
	
	/**
	 * @return null
	 */
	public static function addAlias($alias, $pathname, $allowOverride = true)
	{
		if (isset(self::$aliases[$alias]) && ! self::$aliases[$alias]['allowOverride']) {
			throw new \RuntimeException(sprintf('Cannot override alias %s', $alias));
		}
		
		self::$aliases[$alias] = ['pathname' => $pathname,
			'allowOverride' => $allowOverride];
	}	
	
	/**
	 * @api
	 */
	public function __construct(...$parts)
	{
		$this->pathParts = $parts;
	}
	
	/**
	 * @return bool
	 */
	private function isUnresolved($pathname)
	{
		return strpos($pathname, '@') === false ? false : true;
	}	

	/**
	 * @return bool
	 */
	private function checkCircularRef($input)
	{
		return count(array_unique($input)) != count($input) ? true : false;
	}

	/**
	 * @return string
	 */
	private function expandAlias($pathname)
	{
		$matches = null;		
		if (preg_match_all('/@([^\\\\\\/]+)/', $pathname, $matches)) {
			$search = array();
			$replace = array();
			foreach ($matches[1] as $aliasName) {
				if ( ! isset(self::$aliases[$aliasName])) {
					throw new \RuntimeException(sprintf('Path alias "@%s" has not been defined',
						$aliasName));
				}
				$search[] = '@' . $aliasName;
				$replace[] = self::$aliases[$aliasName]['pathname'];
				$this->resolvedParts[] = $aliasName;
			}
			$pathname = str_replace($search, $replace, $pathname);
		}
		
		return $pathname;
	}
	
	/**
	 * @return string
	 */
	public function read()
	{
		$filename = $this->toString();
		$fd = fopen($filename, 'r');
		IO_Filesystem::assertFd($fd, $filename);
		$content = fread($fd, filesize($filename));
		fclose($fd);
		
		return $content;
	}
	
	/**
	 * @return string
	 */
	public function resolve($pathname)
	{
		$this->resolvedParts = array();

		while ($this->isUnresolved($pathname)) {
			if ($this->checkCircularRef($this->resolvedParts)) {
				throw new \RuntimeException(sprintf('A circular reference has been detected while resolving path %s',
					$this->pathname));
			}
			$pathname = $this->expandAlias($pathname);
		}

		return $pathname;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * @return string|false
	 */
	public function toString()
	{
		$this->pathname = join(DIRECTORY_SEPARATOR, $this->pathParts);		
		$resolved = $this->resolve($this->pathname);
		if ( !empty($this->fileExt)) {
			$resolved .= '.' . $this->fileExt;
		}
		
		return $resolved;
	}
	
	/**
	 * @return bool
	 */
	public function fileExists()
	{
		return file_exists($this->toString());
	}
	
	/**
	 * @return bool
	 */
	public function dirExists()
	{
		return is_dir($this->toString());
	}
	
	/**
	 * @return void
	 */
	public function addExt($ext)
	{
		$this->fileExt = $ext;
	}
	
	/**
	 * Adds a segment to the path
	 * 
	 * @return $this
	 */
	public function addPart($pathPart)
	{
		$this->pathParts = array_merge($this->pathParts, (array)$pathPart);
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getBasename()
	{
		return basename($this->toString());
	}
	
	/**
	 * @return string
	 */
	public function getDirname()
	{
		return dirname($this->toString());
	}
	
	/**
	 * @return void
	 */
	public function serialize($data)
	{
		$filename = $this->toString();		
		$fd = fopen($filename, 'w');
		IO_Filesystem::assertFd($fd, $filename);
		fwrite($fd, serialize($data));
		fclose($fd);
	}
	
	/**
	 * @return mixed
	 */
	public function unserialize()
	{
		return unserialize($this->read());
	}
	
	/**
	 * @return string
	 */
	public function getFileContent()
	{
		return file_get_contents($this->toString());
	}

	/**
	 * @return mixed
	 */
	public function _require($context = null)
	{
		if ($context) {
			if ( ! is_object($context)) {
				throw new \RuntimeException(sprintf('Context must be an object, %s is given',
					gettype($context)));
			}
			
			$filename = $this->toString();
			$closure = \Closure::bind(function() use ($filename) {
				return require $filename;
			}, $context, $context);
			
			return $closure();
		} else {
			return require $this->toString();
		}
	}

	/**
	 * @return string
	 */
	public function readJson()
	{
		$jsonData = json_decode($this->read(), true);
		
		return $jsonData;
	}
}
