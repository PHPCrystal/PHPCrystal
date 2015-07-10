<?php
namespace PHPCrystal\PHPCrystal\Component\MVC\Controller\Action;

use PHPCrystal\PHPCrystal\Service\Twig\Twig;

abstract class AbstractTwigAction extends AbstractAction
{
	private $twig;
	
	public function __construct(Twig $twig = null)
	{
		parent::__construct();
		$this->twig = $twig;
	}

	/**
	 * @return void
	 */
	public function init()
	{
		parent::init();
	}
	
	/**
	 * @return string
	 */
	final public function render($tplName, $variables)
	{
		return $this->twig->render($tplName, $variables);
	}
}
