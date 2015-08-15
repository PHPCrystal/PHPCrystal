<?php
namespace PHPCrystal\PHPCrystal\Service\Router;

use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Service\Router\AbstractRouter;

class _Default extends AbstractRouter
{
	/**
	 * @return void
	 */
	public function init()
	{
		parent::init();
	}
	
	public function handle(Event\Type\Http\Request $event)
	{
		if ( ! parent::matchRequest($event->getRequest())) {
			return false;
		}
		
		foreach ($this->getApplication()->getValidActions() as $action) {
			if ( $action->matchRequest($event->getRequest())) {
				$this->action = $action;
				
				$this->controller = $this->getFactory()
					->createControllerByAction($action);
				
				$this->frontController = $this->getFactory()
					->createFrontControllerByAction($action);

				return true;
			}
		}

		$this->triggerResponse404($event, Event\Type\Http\Response404::create());		
		
		return false;
	}
}