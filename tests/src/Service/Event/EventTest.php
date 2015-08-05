<?php
namespace PHPCrystal\PHPCrystalTest;

use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystalTest\Facade\Dummy;
use PHPCrystal\PHPCrystalTest\_Trait\MakeRequest;

class EventTest extends TestCaseDummy
{
	public function testAssignment()
	{
		$action = $this->createIndexAction()
			->addEventListener(Event\Type\Dummy::toType(), function() {
				$this->startTransaction = true;
				return $this->startTransaction;
			});
		$event = $action->dispatch(Event\Type\Dummy::create());
		$this->assertTrue($event->getResult());
	}
	
	public function testInitServiceEvent()
	{		
		$this->assertEquals('blah blah blah', Dummy::saySomething());

		$this->appPkg->addEventListener(Event\Type\System\InitService::toType(), function($event) {
			$pkgName = $event->getCurrentNode()->getComposerName();
			if ($event->getShortClassName() == 'Dummy') {
				return function() use($pkgName) {
					$this->pkgName = $pkgName;
					$this->sentence = 'I hope it will work';
				};
			}
		});
		
		$ext = $this->appPkg->getExtensions()[0];
		$this->assertEquals(spl_object_hash($ext),
			spl_object_hash($this->appPkg->getChildNodes()[0]));
		// this event handler would not be triggered
		$ext->addEventListener(Event\Type\System\InitService::toType(), function($event) {
			$pkgName = $event->getCurrentNode()->getComposerName();			
			if ($event->getShortClassName() == 'Dummy') {
				return function() use($pkgName) {
					$this->pkgName = $pkgName;
					$this->sentence = "Wanna sleep";
				};
			}
		});			
		// fire event by creating a service instance
		$dummy = Dummy::create();
		$this->assertEquals('I hope it will work', $dummy->saySomething());
		$this->assertEquals('phpcrystal/phpcrystaltest', $dummy->getPackageName());
	}
	
	
	public function testExtensionInstallation()
	{
		$extInstallationEvt = Event\Type\System\ExtensionInstall::create()
			->setComposerPackageName('phpcrystal/phpcrystal');
		
		$this->appPkg->dispatch($extInstallationEvt);
	}
}
