<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type;

use PHPCrystal\PHPCrystal\Service\Event as Event;

class Dummy extends AbstractExternal implements InternalEventInterface
{
	public function __construct()
	{
		parent::__construct();
		$this->type = Event\TYPE_BROADCAST_LEVEL_ORDER;
		$this->singleResultFlag = true;
	}
	
	public function createContext()
	{
		return Event\Context\Dummy::create('dummyContext');
	}

	/**
	 * @return void
	 */
	public function output() {  }
}
