<?php
namespace PHPCrystal\PHPCrystal\Component\MVC\Controller;

use PHPCrystal\PHPCrystal\Component\Container\AbstractContainer;
use PHPCrystal\PHPCrystal\Facade as Facade;

class Input extends AbstractContainer
{
	public function __construct(array $items = array())
	{
		parent::__construct($items);
	}

	public function init()
	{
		$this->set('UID', Facade\Session::create()->getUserId());
	}
}
