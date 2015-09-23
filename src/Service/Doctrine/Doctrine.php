<?php
namespace PHPCrystal\PHPCrystal\Service\Doctrine;

use PHPCrystal\PHPCrystal\Component\Service\AbstractService;
use PHPCrystal\PHPCrystal\Component\Factory as Factory;
use Doctrine\ORM\Tools\Setup;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration as OrmConf;
use Doctrine\ORM\Events;
use Doctrine\DBAL\DriverManager;

use PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper;

use Doctrine\Common\ClassLoader,
    Doctrine\ORM\Configuration,
    Doctrine\ORM\EntityManager,
    Doctrine\Common\Cache\ArrayCache,
    Doctrine\DBAL\Logging\EchoSQLLogger;

class Doctrine extends AbstractService
{
	private $config;
	private $pkgConfig;
	private $eventManager;
	private $entityManager;
	private $ormConfig;

	/**
	 * {@inherited}
	 */
	public static function hasLazyInit()
	{
		return true;
	}

	/**
	 * {@inherited}
	 */
	public static function isSingleton()
	{
		return true;
	}

	/**
	 * @return void
	 */
	public function init()
	{
		if ($this->isInitialized()) {
			return;
		}
		
		$context = $this->getApplication()->getContext();
		
		$this->serviceConfig = $this->getServiceConfig();
		
		$this->config = $this->getServiceConfig();

		$this->eventManager = new EventManager();
		
		$conn = $context->pluck('phpcrystal.phpcrystal.database')
			->toArray(); // database connection config

		$isDevEnv = $context->getEnv() == 'dev';
		$this->ormConfig = Setup::createAnnotationMetadataConfiguration([], $isDevEnv);
		$this->ormConfig = new Configuration();
		$cache = new ArrayCache;
		$this->ormConfig->setMetadataCacheImpl($cache);

		$driverImpl = $this->ormConfig->newDefaultAnnotationDriver([], false);

		$this->ormConfig->setMetadataDriverImpl($driverImpl);
		$this->ormConfig->setQueryCacheImpl($cache);
		$this->ormConfig->setQueryCacheImpl($cache);

		if (null != ($entityNSArray = $this->config->get('entityNamespaces'))) {
			$this->ormConfig->setEntityNamespaces($entityNSArray);
		}

		// Proxy configuration
		$this->ormConfig->setProxyDir($this->config->get('proxyDir'));
		$this->ormConfig->setProxyNamespace($this->config->get('proxyNamespace'));		
		$this->entityManager = $this->createEntityManager($conn, $this->ormConfig, $this->eventManager);
		
		$this->isInitialized = true;
		
		return $this;
	}

	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function createEntityManager($conn, OrmConf $config = null, $eventManager = null)
	{
		if (empty($config)) {
			$config = new OrmConf();
			$config->setAutoCommit($this->autoCommit);

			$annotationDriver = $config->newDefaultAnnotationDriver($this->entitiesPaths);
			$config->setMetadataDriverImpl($annotationDriver);
					
			$config->setProxyDir($this->proxyDir);
			$config->setProxyNamespace($this->proxyNamespace);
		}
		
		return EntityManager::create($conn, $config, $eventManager);
	}

	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}

	/**
	 * @return Doctrine\DBAL\Connection
	 */
	public function getConnection(array $params, $config = null)
	{
		$conn = DriverManager::getConnection($params, $config);
		
		return $conn;
	}
}
