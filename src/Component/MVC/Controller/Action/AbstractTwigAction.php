<?php
namespace PHPCrystal\PHPCrystal\Component\MVC\Controller\Action;

use PHPCrystal\PHPCrystal\Contract as Contract;
use PHPCrystal\PHPCrystal\Service\Twig\Twig;

abstract class AbstractTwigAction extends AbstractAction
{
	private $twig;
	
	public function __construct(Contract\Cache $cache, Contract\Session $session, Twig $twig = null)
	{
		parent::__construct($cache, $session);
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
