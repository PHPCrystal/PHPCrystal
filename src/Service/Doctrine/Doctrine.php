<?php
namespace PHPCrystal\PHPCrystal\Service\Doctrine;

use PHPCrystal\PHPCrystal\Component\Service\AbstractService;
use PHPCrystal\PHPCrystal\Component\Factory as Factory;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration as OrmConf;
use Doctrine\ORM\Events;

class Doctrine extends AbstractService
{
	private $config;
	private $eventManager;
	private $autoCommit;
	private $proxyDir;
	private $proxyNamespace;
	private $modelNamespace;
	private $modelPaths;
	private $entitiesPaths;
	
	private $entityManager;
	
	public static function hasLazyInit()
	{
		return true;
	}
	
	public static function isSingleton()
	{
		return true;
	}

	public function init()
	{
		if ($this->isInitialized()) {
			return;
		}
		
		$context = $this->getApplication()->getContext();
		$opts = $context->pluck('phpcrystal.core.doctrine');
		
		$this->proxyDir = $opts->get('proxyDir');
		$this->proxyNamespace = $opts->get('proxyNamespace');
		$this->modelNamespace  = $opts->get('modelNamespace');
		$this->modelPaths = $opts->get('modelPaths');
		$this->entitiesPaths = $opts->get('entitiesPaths');
		$this->autoCommit = $opts->get('dbal.autocommit');
		
		$this->getApplication()->getAutoloader()
			->addPsr4($this->modelNamespace, $this->modelPaths);

		$this->eventManager = new EventManager();
		
		$conn = $context->pluck('phpcrystal.core.database')
			->toArray(); // database connection config
		$isDevEnv = $context->getEnv() == 'dev';
		//$this->config = Setup::createAnnotationMetadataConfiguration($this->modelPaths, $isDevEnv);
		$this->entityManager = $this->createEntityManager($conn, null, $this->eventManager);
		
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
}
