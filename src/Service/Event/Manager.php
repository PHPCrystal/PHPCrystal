<?php
namespace PHPCrystal\PHPCrystal\Service\Event;

use PHPCrystal\PHPCrystal\Component\Factory as Factory;
use PHPCrystal\PHPCrystal\Component\Service\AbstractService;

const PHASE_INIT = 1;
const PHASE_PREDISPATCH = 2;
const PHASE_DOWN = 3;
const PHASE_TERMINATE_NODE = 4;
const PHASE_UP = 5;
const PHASE_POSTDISPATCH = 6;
const PHASE_FINISHED = 7;

const STATUS_NONE = 1;
const STATUS_DISPATCHED = 2;
const STATUS_DISCARDED = 3;
const STATUS_INTERRUPTED = 4;

const TYPE_BROADCAST_POST_ORDER = 1;
const TYPE_BROADCAST_LEVEL_ORDER = 2;
const TYPE_UNICAST_BIDIRECTIONAL = 3;
const TYPE_UNICAST_SINGLE_DIRECTIONAL = 4;
const TYPE_UNICAST_SINGLE_DIRECTIONAL_REVERSE = 5;

const RESULT_TYPE_ALLOW_OVERWRITE = 1;
const RESULT_TYPE_SINGLE = 2;
const RESULT_TYPE_SINGLE_CLOSURE = 3;
const RESULT_TYPE_STACKED = 4;

final class Manager extends AbstractService
{
	protected $eventHandlers = array();
	
	public static function isSingleton()
	{
		return true;
	}

	/**
	 * @return boolean
	 */
	protected function checkEventType($eventClassName)
	{
		if ( ! is_subclass_of($eventClassName,
			'PHPCrystal\\PHPCrystal\\Service\\Event\\Type\\AbstractEvent'))
		{
			throw new \Exception('event does not exists');
		}
	}
	
	/**
	 * Return an array of event handlers of the given node
	 * 
	 * @return array
	 */
	protected function getHandlers($event, $node)
	{
		$nodeKey = spl_object_hash($node);
		if ( ! isset($this->eventHandlers[$nodeKey])) {
			return array();
		}
		
		$eventType = $event::toType();
		foreach ($this->eventHandlers[$nodeKey] as $type => $handlerArray) {
			if ($type == $eventType) {
				return $handlerArray;
			}
		}

		return array();
	}
	
	/**
	 * @return boolean
	 */
	protected function callHandler($handler, $event, $node)
	{
		if ( ! ($handler instanceof \Closure)) {
			return null;
		}		

		$event->setCurrentNode($node);
		$newHandler = $handler->bindTo($node, $node);
		$result = $newHandler($event);		
		
		$event->setResult($result);		
		$event->setStatus(STATUS_DISPATCHED);

		return $result;
	}

	/**
	 * Executes all handlers attached to the given node
	 * 
	 * @return boolean
	 */
	public function trigger($event, $node)
	{
		foreach ($this->getHandlers($event, $node) as $nodeHandler) {
			$this->callHandler($nodeHandler, $event, $node);
		}
	}
	
	/**
	 * @return boolean
	 */
	public function triggerTerminateNode($event, $node)
	{	
		$event->setPhase(PHASE_TERMINATE_NODE);
		return $this->callHandler($event->getTerminateNodeHandler(), $event, $node);
	}

	/**
	 * @return $this
	 */
	public function addEventListener($eventType, $node, \Closure $handler)
	{
		$this->checkEventType($eventType);
		
		$nodeKey = spl_object_hash($node);
		if ( ! isset($this->eventHandlers[$nodeKey])) {
			$this->eventHandlers[$nodeKey] = array();
		}
		
		if ( ! isset($this->eventHandlers[$nodeKey][$eventType])) {
			$this->eventHandlers[$nodeKey][$eventType] = array();
		}
		
		$this->eventHandlers[$nodeKey][$eventType][] = $handler;
		
		return $this;
	}
	
	private function checkEvent($event)
	{
		if ($event->getStatus() == STATUS_DISCARDED ||
			$event->getPhase() >= PHASE_POSTDISPATCH ||
			($event->hasResult() && ($event->getResultType() == RESULT_TYPE_SINGLE ||
				$event->getResultType() == RESULT_TYPE_SINGLE_CLOSURE)))
		{
			return false;
		} else {
			return true;
		}
	}

	/**
	 * @return boolean
	 */
	public function hasEventListener($event, $node)
	{
		return count($this->getHandlers($event, $node)) > 0 ? true : false;
	}
	
	private function walkNodes($event, $nodesArray)
	{
		$lastNode = null;
		
		foreach ($nodesArray as $node) {
			if ( ! $this->hasEventListener($event, $node)) {
				continue;
			}

			$lastNode = $node;
			$this->trigger($event, $node);

			if ( ! $this->checkEvent($event)) {
				return;
			} else if ($event->getStopPropagationFlag()) {
				return $lastNode;
			}
		}
		
		return $lastNode;
	}
	
	/**
	 * @return 
	 */
	private function dispatchSingleDirectional($event, $target)
	{
		$event->setPhase(PHASE_DOWN);
		return $this->walkNodes($event, $target->getPropagationPath());
	}
	
	/**
	 * 
	 */
	private function dispatchSingleDirectionalReverse($event, $target, $offset = null)
	{
		$event->setPhase(PHASE_UP);		
		return $this->walkNodes($event,
			array_reverse($target->getPropagationSubPath($offset)));
	}
	
	private function dispatchBidirectional($event, $target)
	{	
		$lastNode = $this->dispatchSingleDirectional($event, $target);		
		if ( ! $this->checkEvent($event)) {
			return;
		}

		$event->resetStopPropagationFlag();
		if ( ! $lastNode ) {
			$lastNode = $target->getTerminateNode();
		}

		$this->triggerTerminateNode($event, $target->getTerminateNode());
		if ($event->getStatus() == STATUS_DISCARDED) {
			return;
		}		

		$this->dispatchSingleDirectionalReverse($event, $target, $lastNode);		
	}

	/**
	 * @return void
	 */
	private function treeLevelOrderTraversal($event, $nodesArray)
	{
		$nextLevel =  [];
		
		foreach ($nodesArray as $node) {
			$this->trigger($event, $node);			
			if ( ! $this->checkEvent($event) ||
				$event->getStopPropagationFlag())
			{
				return;
			} else {			
				$nextLevel = array_merge($nextLevel, $node->getChildNodes());
			}
		}
		
		if (count($nextLevel)) {
			$this->treeLevelOrderTraversal($event, $nextLevel);			
		}	
	}

	/**
	 * @return void
	 */
	private function treePostOrderTraversal($event, $target)
	{
		foreach ($target->getChildNodes() as $node) {
			$this->treePostOrderTraversal($event, $node);
		}
		
		if ($this->checkEvent($event) && ! $event->getStopPropagationFlag()) {
			$this->trigger($event, $target);
		}
	}

	private function dispatchHelper($event, $target)
	{
		$event->setTarget($target);

		// Call onPreDispatch method and change event phase
		if ($event->getPhase() == PHASE_INIT) {
			$event->setPhase(PHASE_PREDISPATCH);
			$event->onPreDispatch();
			$event->setPhase(PHASE_DOWN);
		} else if ($event->getPhase() == PHASE_PREDISPATCH) {
			$event->setPhase(PHASE_DOWN);
		}

		if ($event->getStatus() != STATUS_NONE) {
			return;
		}

		switch ($event->getType()) {
			case TYPE_UNICAST_BIDIRECTIONAL:
				$this->dispatchBidirectional($event, $target);
				break;
			
			case TYPE_UNICAST_SINGLE_DIRECTIONAL:
				$this->dispatchSingleDirectional($event, $target);
				break;
			
			case TYPE_UNICAST_SINGLE_DIRECTIONAL_REVERSE:
				$this->dispatchSingleDirectionalReverse($event, $target);
				break;

			case TYPE_BROADCAST_LEVEL_ORDER: 
				$this->treeLevelOrderTraversal($event, [$target]);
				break;
			
			case TYPE_BROADCAST_POST_ORDER:
				$this->treePostOrderTraversal($event, $target);
				break;
		}

		//if ($event->getPhase() == PHASE_UP) {
		//	$event->setPhase(PHASE_POSTDISPATCH);
			$event->onPostDispatch();
		//}
		
		$event->setPhase(PHASE_FINISHED);
	}
	
	/**
	 * @return Event
	 */
	public function dispatch($event, $target)
	{
		try {
			if ($event->getStatus() != STATUS_NONE ||
				$event->getPhase() == PHASE_FINISHED)
			{
				return $event;
			}
			$this->dispatchHelper($event, $target);
		} catch (Exception\AbstractException $e) {
			$e->setPackage($this);
		} catch (\Exception $e) {
			var_dump($e); exit;
			$legacyExcep = Exception\System\Legacy::create()
				->setLegacyException($e)
				->setPackage($this);
			$event->setException($legacyExcep);
			$event->setStatus(Event\STATUS_INTERRUPTED);
		} finally {
			if ($event->hasAutoTriggerEvent()) {
				$newEvent = $event->getAutoTriggerEvent();
				$newEvent->setLastDispatchedEvent($event);
				return $this->dispatch($newEvent, $target);
			} else {
				return $event;
			}
		}
	}
}
