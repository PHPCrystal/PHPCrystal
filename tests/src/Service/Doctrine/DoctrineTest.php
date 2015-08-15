<?php
namespace PHPCrystal\PHPCrystalTest\Service\Doctrine;

use PHPCrystal\PHPCrystalTest\TestCaseDummy;
use PHPCrystal\PHPCrystal\Facade\Doctrine;
use PHPCrystal\PHPCrystalTest\Model\Entity\User;

class DoctrineTest extends TestCaseDummy
{
	private $doctrine;
	
	public function setUp()
	{
		parent::setUp();
		$this->doctrine = Doctrine::create();
	}

	public function testConnection()
	{
		$params = $this->appPkg->getContext()
			->pluck('phpcrystal.phpcrystal.database')->toArray();
		$conn = $this->doctrine->getConnection($params);
		$conn->connect();
		$this->assertTrue($conn->isConnected());
		$dbNamesArray = $conn->fetchAll('show databases');
		$this->assertTrue(count($dbNamesArray) > 0);
	}
	
	public function testCreateNewUser()
	{
		$this->doctrine->init();
		$em = $this->doctrine->getEntityManager();
		
		$newUser = new User();
		$em->persist($newUser);
		$em->flush();
	}
	
	public function testUpdateUser()
	{
		$this->doctrine->init();
		$em = $this->doctrine->getEntityManager();
		
		$user = $em->find('MyRepo:User', 1);
		
		//var_dump($user);
	}
}
