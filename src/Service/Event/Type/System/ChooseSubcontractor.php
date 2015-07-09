<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\System;

use PHPCrystal\PHPCrystal\Service\Event as Event;

class ChooseSubcontractor extends Event\Type\AbstractInternal
{
	/**
	 * @var string
	 */
	private $contractorInstance;
	
	final public function __construct($contractorInstance)
	{
		parent::__construct();
		$this->type = Event\TYPE_UNICAST_SINGLE_DIRECTIONAL_REVERSE;
		$this->resultType = Event\RESULT_TYPE_SINGLE;
		$this->contractorInstance = $contractorInstance;
	}
}
