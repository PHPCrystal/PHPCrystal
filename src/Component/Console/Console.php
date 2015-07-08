<?php
namespace PHPCrystal\PHPCrystal\Component\Console;

use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Service\Event\Type\Cli\Command as CommandEvent;
use Symfony\Component\Console\Application as SymfonyConsole;
use PHPCrystal\PHPCrystal\Component\Console\Input\ArgvInput;
use PHPCrystal\PHPCrystal\Component\Console\Input\Definition;
use PHPCrystal\PHPCrystal\Component\Console\Output\ConsoleOutput;

use PHPCrystal\PHPCrystal\Component\Package\Option\Container;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Console extends SymfonyConsole
{
	/**
	 * @var string
	 */
	protected static $cmdClassNamespace = '\\PHPCrystal\\PHPCrystal\\Component\\Console\\Command';

	private $application;
	private $input;
	private $output;
	private $commandInstance;
	private $commandName;

	private function getCommandClassName($input)
	{		
		$cmdClassName = static::$cmdClassNamespace . '\\' .
			str_replace(':', '', preg_replace_callback('/\b([a-z])/', function($matches) {
					return strtoupper($matches[1]);
				}, $input->getFirstArgument())
			);

		return $cmdClassName;
	}
	
	/**
	 * @return void
	 */
	public static function setCmdClassNamespace($namespace)
	{
		static::$cmdClassNamespace = $namespace;
	}
	
	public function getInput()
	{
		return $this->input;
	}
	
	public function getOutput()
	{
		return $this->output;
	}

	/**
	 * @return
	 */
	public function getCommandInstance()
	{
		return $this->commandInstance;
	}
	
    protected function getDefaultInputDefinition()
    {
		$definition = parent::getDefaultInputDefinition();
		
		$definition->addOption(Input\Option::create('--env',
			'', Input\Option::VALUE_REQUIRED, 'Set environment variable', 'dev'));
		
		return $definition;	
    }
	
	/**
	 * @return $this
	 */
	public static function create($application)
	{		
		$consoleApp = new static('myapp', '1.0 (stable)');
		$consoleApp->application = $application;
		
		return $consoleApp;
	}
	
	/**
	 * @return
	 */
	public function getApplicationPackage()
	{
		return $this->application;
	}

	/**
	 * @return $this
	 */
	public function run(InputInterface $input = null, OutputInterface $output = null)
	{
		if (null === $input) {
			$this->input = new ArgvInput();
		}
		
		if (null === $output) {
			$this->output = new ConsoleOutput();
		}

		$className = $this->getCommandClassName($this->input);
		if (class_exists($className)) {
			$this->commandName = $this->getCommandName($this->input);
			$this->commandInstance = $className::create($this->commandName);
			$this->commandInstance
				->setApplication($this);
			$this->add($this->commandInstance);
		} else {
			$commandEvent = Event\Type\Dummy::create();
		}

		// Dispatch event
		$cliArgsContainer = new Container('cliArgsContainer');
		if ($this->input->hasParameterOption('--env')) {
			$cliArgsContainer->set('env', $this->input->getParameterOption('--env'));
		}
		
		$commandEvent = CommandEvent::create($this, $cliArgsContainer);
		$this->application->dispatch($commandEvent);

		// Run command
		if ($commandEvent->getStatus() != Event\STATUS_DISCARDED) {
			$this->doRun($this->input, $this->output);			
		}
	}
}
