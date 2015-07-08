<?php
namespace PHPCrystal\PHPCrystal\Component\MVC\Controller\Action;

use PHPCrystal\PHPCrystal\Component\Http\Request;
use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Facade as Facade;
use PHPCrystal\PHPCrystal\Service\Twig\Twig;

abstract class AbstractTwigAction extends AbstractAction
{
	private $twig;
	
	public function __construct(Twig $twig = null)
	{
		parent::__construct();
		$this->twig = $twig;
	}

	public function init()
	{
		parent::init();
	}
	
	public function render($tplName, $variables)
	{
		return $this->twig->render($tplName, $variables);
	}
}
