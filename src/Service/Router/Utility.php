<?php
namespace PHPCrystal\PHPCrystal\Service\Router;

use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Service\Router\AbstractRouter;
use PHPCrystal\PHPCrystal\Component\Http\Request;

class Utility extends AbstractRouter
{
	public function processIndexRequest(Request $request)
	{
		
	}
	
	public function handle(Event\Type\Http\Request $event)
	{
		return false;
	}
	
	public function processRequest(Request $request)
	{

	}
}
