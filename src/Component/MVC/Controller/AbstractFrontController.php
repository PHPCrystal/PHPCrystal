<?php
namespace PHPCrystal\PHPCrystal\Component\MVC\Controller;

use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Component\Factory as Factory;

abstract class AbstractFrontController extends Event\AbstractAppListener
{
	/**
	 * @return void
	 */
	public function init()
	{
		$this->addEventListener(Event\Type\Http\Request::toType(), function($event) {
			if ($event->getPhase() == Event\PHASE_DOWN) {
				return $this->onBeforeExecution($event);
			} else if ($event->getPhase() == Event\PHASE_UP) {
				return $this->onAfterExecution($event);
			}
		});		
	}
	
	//
	// Event hooks
	//
	
	/**
	 * @return void
	 */
	protected function onBeforeExecution($event) {  }
	
	/**
	 * @return void
	 */
	protected function onAfterExecution($event) {  }
}
