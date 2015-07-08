<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type;

abstract class AbstractExternal extends AbstractEvent
{
	public function __construct()
	{
		parent::__construct();
	}

	//
	// Abstract methods
	//

	/**
	 * @return \PHPCrystal\PHPCrystal\Service\Event\Context\AbstractContext
	 */
	abstract public function createContext();	
}
