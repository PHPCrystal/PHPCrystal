<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\Cli;

use PHPCrystal\PHPCrystal\Service\Event\Context\Cli;
use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Component\Console\Console;
use PHPCrystal\PHPCrystal\Component\Package\Option\Container;

class Command extends Event\Type\AbstractExternal
{
	private $commandInstance;
	private $commandName;
	private $consoleApp;	
	private $cliArgsContainer;
	
	public function __construct(Console $consoleApp, Container $cliArgsContainer = null)
	{
		parent::__construct();
		$this->type = Event\TYPE_BROADCAST_LEVEL_ORDER;
		
		$this->cliArgsContainer = $cliArgsContainer;
		$this->consoleApp = $consoleApp;
		$this->commandInstance = $consoleApp->getCommandInstance();
		$this->commandName = $this->commandInstance && $this->commandInstance->getName();
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Service\Event\Context\Cli
	 */
	public function createContext()
	{
		$context = Cli::create('cliContext');
		if (null !== $this->cliArgsContainer) {
			$context->merge($this->cliArgsContainer);
		}

		return $context;
	}

	/**
	 * @return string
	 */
	public function getCommandName()
	{
		return $this->commandName;
	}
	
	/**
	 * @return PHPCrystal\PHPCrystal\Component\Console\Command\AbstractCommand
	 */
	public function getCommandInstance()
	{
		return $this->commandInstance;
	}
	
	/**
	 * @return void
	 */
	public function output()
	{

	}
}
