<?php
namespace PHPCrystal\PHPCrystal\Service\Twig;

use PHPCrystal\PHPCrystal\Service\View\Helper;
use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Component\Service\AbstractService;

class Twig extends AbstractService
{
	private $twig;
	private $twigLoader;
	private $options;
	private $viewHelper;
	
	/**
	 * @return array
	 */
	public static function getWakeupEvents()
	{
		return [new Event\Type\Http\Request()];
	}

	public function __construct(Helper $helper = null)
	{
		parent::__construct();
		$this->viewHelper = $helper;
	}

	public function init()
	{
		$this->options = $this->getApplication()
			->getContext()->pluck('phpcrystal.core.twig');

		$this->twig = new \Twig_Environment(new Loader($this->options->get('templates')),
			$this->options->toArray());		
		$this->twig->addGlobal('phpcrystal', $this->viewHelper);
	}
	
	public function setTwigLoader(\Twig_LoaderInterface $loader)
	{
		$this->twigLoader = $loader;
	}
	
	public function render($tplName, $vars = array())
	{
		return $this->twig->render($tplName, $vars);
	}
}
