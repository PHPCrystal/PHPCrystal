<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type\System;

use PHPCrystal\PHPCrystal\Service\Event as Event;

class PkgNotification extends Event\Type\AbstractInternal
{
	private $notifWord;
	
	final public function __construct($word)
	{
		parent::__construct();
		$this->notifWord = $word;
		$this->type = Event\TYPE_BROADCAST_POST_ORDER;
	}
	
	/**
	 * @return string
	 */
	public function getNotifWord()
	{
		return $this->notifWord;
	}
}
