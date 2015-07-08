<?php
namespace PHPCrystal\PHPCrystal\Component\Filesystem;

use PHPCrystal\PHPCrystal\_Trait\CreateObject;

class PathResolver
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
	
	public function __construct(...$parts)
	{
		foreach ($parts as $part) {
			$this->pathParts = array_merge($this->pathParts, explode(DIRECTORY_SEPARATOR, $part));
		}
	}
	
	/**
	 * @return boolean
	 */
	protected function checkCircularRef($input)
	{
		return count(array_unique($input)) != count($input) ? true : false;
	}
	
	/**
	 * @return string
	 */
	protected function expandAlias($pathname)
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
	 * @return boolean
	 */
	protected function isUnresolved($pathname)
	{
		return strpos($pathname, '@') !== false ? true : false;
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
	 * @return boolean
	 */
	public function fileExists()
	{
		$filename = $this->toString();
		
		return file_exists($filename);
	}
	
	/**
	 * @return boolean
	 */
	public function dirExists()
	{
		return $this->fileExists();
	}
	
	public function addExt($ext)
	{
		$this->fileExt = $ext;
	}
	
	public function addPart($pathPart)
	{
		$this->pathParts = array_merge($this->pathParts, (array)$pathPart);
		
		return $this;
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
		$content = file_get_contents($this->toString());
		
		$jsonData = json_decode($content, true);
		
		return $jsonData;
	}
	
	/**
	 * @return string
	 */
	public function getBasename()
	{
		return basename($this->toString());
	}
	
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
		fwrite($fd, serialize($data));
		fclose($fd);
	}
	
	/**
	 * @return mixed
	 */
	public function unserialize()
	{
		$filename = $this->toString();
		
		if ($this->fileExists()) {
			return unserialize(file_get_contents($filename));
		} else {
			return null;
		}
	}
	
	/**
	 * @return string
	 */
	public function getFileContent()
	{
		return file_get_contents($this->toString());
	}
}
