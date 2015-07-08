<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\System;

use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Facade as Facade;

class Build extends Event\Type\AbstractInternal
{
	public function __construct()
	{
		parent::__construct();
		$this->type = Event\TYPE_BROADCAST_POST_ORDER;
	}
	
	public function onPostDispatch()
	{
		Facade\Metadriver::save();
	}
}
