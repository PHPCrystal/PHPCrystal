<?php
namespace PHPCrystal\PHPCrystal\Component\MVC\Controller;

use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Contract as Contract;
use PHPCrystal\PHPCrystal\Service as Service;

abstract class AbstractQuartzController extends AbstractController
{
	private $doctrine;

	/**
	 * @api
	 */
	public function __construct(Contract\Cache $cache, Contract\Session $session,
		Service\Doctrine\Doctrine $doctrine)
	{
		parent::__construct($cache, $session);
		$this->doctrine = $doctrine;
	}

	/**
	 * @return \PHPCrystal\PHPCrystal\Service\Doctrine\Doctrine
	 */
	protected function getDoctrine()
	{
		$this->init();
		
		return $this->doctrine;
	}
}
