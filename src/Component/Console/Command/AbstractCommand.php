<?php
namespace PHPCrystal\PHPCrystal\Component\Console\Command;

use PHPCrystal\PHPCrystal\Service\Event\Type\AbstractExternal;
use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Component\Factory as Factory;

use PHPCrystal\PHPCrystal\Component\Console as Console;
use PHPCrystal\PHPCrystal\Component\Console\Input\Option;
use PHPCrystal\PHPCrystal\Component\Console\Input\Argument;
use PHPCrystal\PHPCrystal\Component\Console\Input\Definition;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

use PHPCrystal\PHPCrystal\_Trait\CreateObject;

abstract class AbstractCommand extends SymfonyCommand
{
	use CreateObject;
	
	public  function setResultSortable()
	{
		$this->getDefinition()->setOptions(array(
			Option::create('--sort-by', '-s', Option::VALUE_REQUIRED, 'Sort output by the given field'),
			Option::create('--sort-mode', '', Option::VALUE_REQUIRED, 'Sort mode', 'asc')
		));
	}
	
	/**
	 * @return
	 */
	protected function sortBy(&$data)
	{
		$input = $this->getApplication()->getInput();
		if (empty($data) || ! $input->hasOption('sort-by')) {
			return;
		}

		$sortByKey = $input->getOption('sort-by');		
		if ( ! is_array($data[0]) || ! isset($data[0][$sortByKey])) {
			return;
		}

		$sortMode = $input->hasOption('sort-mode') ?
			$input->getOption('sort-mode') :
			$input->getOption('sort-mode')->getDefault();

		usort($data, function($arrayItemA, $arrayItemB) use ($sortByKey, $sortMode)  {
			$a = $arrayItemA[$sortByKey];
			$b = $arrayItemB[$sortByKey];
			if ($a === $b) {
				return 0;
			}
			if ($sortMode == 'asc') {
				return $a < $b ? 1 : -1;
			} else {
				return $a < $b ? -1 : 1;
			}
		});		
	}
}
