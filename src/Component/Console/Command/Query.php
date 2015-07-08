<?php
namespace PHPCrystal\PHPCrystal\Component\Console\Command;

use PHPCrystal\PHPCrystal\Component\Console\Input as Input;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Query extends AbstractCommand
{
    private $command;
	
	private function getActionUriParams($action)
	{
		$result = array();
		
		$refMethod = new \ReflectionMethod($action, 'defineReverseUri');
		foreach ($refMethod->getParameters() as $param) {
			$paramPlaceholder = '<';
			$paramPlaceholder .= strtolower($param->name);
			if ($param->isOptional()) {
				$paramPlaceholder .= ':'. $param->getDefaultValue();
			}
			$paramPlaceholder .= '>';
			$result[] = $paramPlaceholder;
		}

		return $result;
	}

	/**
	 * @return void
	 */
	protected function showRoutes()
	{
		$table = $this->getHelper('table');
		$table->setHeaders(['Action (action)', 'Route (route)', 'URI path regexp']);		
		$tableRows = array();		
		$appPkg = $this->getApplication()->getApplicationPackage();
		
		foreach ($appPkg->getAllActions() as $action) {
			$row = array();
			$row['action'] = $action->getName();
			$actionParams = $this->getActionUriParams($action);
			$row['route'] = $action->getReverseUri(...$actionParams);
			$row['regexp'] = $action->getUriPathRegExp();
			$tableRows[] = $row;			
		}
		
		$this->sortBy($tableRows);
		$table->addRows($tableRows);

		$table->render($this->getApplication()->getOutput());
	}

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this
            ->setName('query')
            ->setDefinition(array(
                new Input\Argument('command_name', Input\Argument::OPTIONAL, 'The command name', 'help'),
                new Input\Option('xml', null, Input\Option::VALUE_NONE, 'To output help as XML'),
                new Input\Option('format', null, Input\Option::VALUE_REQUIRED, 'The output format (txt, xml, json, or md)', 'txt'),
                new Input\Option('raw', null, Input\Option::VALUE_NONE, 'To output raw command help'),
            ))
            ->setDescription('Information query')
            ->setHelp(<<<EOF
EOF
            )
        ;
		
		$this->setResultSortable();
    }

    /**
     * Sets the command.
     *
     * @param Command $command The command to set
     */
    public function setCommand(Command $command)
    {
        $this->command = $command;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
		$this->showRoutes();
    }	
}