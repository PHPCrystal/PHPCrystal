<?php
namespace PHPCrystal\PHPCrystal\Component\Console\Command;

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use PHPCrystal\PHPCrystal\Facade as Facade;

class Doctrine extends AbstractCommand
{
	protected function defineOptions()
	{
		
	}
	
	public function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output) {
		echo 1;
	}
	
	protected function init()
	{
		$em = Facade\Doctrine::init()
			->getEntityManager();
		
		return ConsoleRunner::createHelperSet($em);
	}

	protected function finish()
	{
		echo 1;
	}
}