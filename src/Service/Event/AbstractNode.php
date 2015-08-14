<?php
namespace PHPCrystal\PHPCrystal\Service\Event;

use PHPCrystal\PHPCrystal\Component\Factory as Factory;
use PHPCrystal\PHPCrystal\_Trait\AFPAware;

abstract class AbstractNode implements
	Factory\Aware\FactoryInterface,
	Factory\Aware\ApplicationInterface,
	Factory\Aware\PackageInterface
{
	use AFPAware;
	
	/**
	 * @var array
	 */
	private $priorEvents = [];
	
	/**
	 * @var \PHPCrystal\PHPCrystal\Service\Event\Type\AbstractEvent
	 */
	private $currentEvent;
	private $dispatchChain = array();

	protected $parentNode;
	protected $childNodes = array();

	/**
	 * @api
	 */
	public function __construct()
	{
		$this->dispatchChainAddElement($this);
	}
	
	protected function getEventManager()
	{
		return $this->getFactory()
			->create('PHPCrystal\\PHPCrystal\\Service\\Event\\Manager');
	}
	
	/**
	 * @return \PHPCrystal\PHPCrystal\Service\Event\Type\AbstractEvent
	 */
	final public function getCurrentEvent()
	{
		return $this->currentEvent;
	}
	
	/**
	 * @return void
	 */
	final public function setCurrentEvent($event)
	{
		return $this->currentEvent = $event;
	}

	/**
	 * @return AbstractEvent
	 */
	public function dispatch($event)
	{
		return $this->getEventManager()->dispatch($event, $this);				
	}
		
	/**
	 * @return $this
	 */
	public function addEventListener($eventType, \Closure $handler)
	{
		$this->getEventManager()
			->addEventListener($eventType, $this, $handler);
		
		return $this;
	}

	/**
	 * @return array
	 */
	final public function getDispatchChain()
	{
		return $this->dispatchChain;
	}
	
	/**
	 * @return array
	 */
	final public function getReverseDispatchChain()
	{
		return array_reverse($this->getDispatchChain());
	}
	
	final public function getPropagationPathPrevNode($offset)
	{
		$prev = null;
		
		foreach ($this->getDispatchChain() as $node) {
			if ($node === $offset) {
				return $prev;
			}
			$prev = $node;
		}
	}
	
	/**
	 * @return array
	 */
	final function sliceDispatchChain($offset, $reverseChain = false)
	{
		$result = array();
		$captureFlag  = false;
		
		$dispatchChain = $reverseChain ?
			$this->getReverseDispatchChain() : $this->getDispatchChain();
		
		foreach ($dispatchChain as $listener) {
			if ($offset === $listener) {
				$captureFlag = true;
			}
			
			if ($captureFlag) {
				$result[] = $listener;
			}
		}
		
		return $result;
	}

	/**
	 * @return $this
	 */
	final function dispatchChainAddElement($node)
	{
		$this->dispatchChain[] = $node;
		
		return $this;
	}
	
	/**
	 * @return $this
	 */
	final public function addChild($node)
	{
		$this->childNodes[] = $node;
		$node->parentNode = $this;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	final public function getChildNodes()
	{
		return $this->childNodes;
	}
	
	/**
	 * @return $this
	 */
	final public function getTerminateNode()
	{
		$path = $this->getDispatchChain();
		
		return end($path);
	}
	
	/**
	 * @return boolean
	 */
	final public function hasChildNodes()
	{
		return count($this->childNodes) ? true : false;
	}
	
	/**
	 * 
	 * @return $this
	 */
	final function getRootNode()
	{
		$root = $this;
		
		while ($root->parentNode) {
			$root = $root->parentNode;
		}
		
		return $root;
	}
	
	/**
	 * @return array
	 */
	private function getDescendentNodesHelper($node)
	{
		$result = array($node);
		
		foreach ($node->getChildNodes() as $child) {
			$result = array_merge($result, $this->getDescendentNodesHelper($child));
		}
		
		return $result;
	}
	
	/**
	 * @return array
	 */
	final public function getDescendentNodes($includeParent = false)
	{
		$result = $this->getDescendentNodesHelper($this);
		
		if ( ! $includeParent ) {
			array_shift($result);
		}
		
		return $result;
	}
	
	/**
	 * @return void
	 */
	final public function addPriorEvent($event)
	{
		$this->priorEvents[$event::toType()] = $event;
	}

	/**
	 * Returns an array of prior events.
	 * 
	 * A prior event is an event that is triggered before the main event will be
	 * dispatched to the current node
	 * 
	 * @return array
	 */
	final public function getPriorEvents($parentEventPhase)
	{
		$result = [];
		
		foreach ($this->priorEvents as $type => $priorEvent) {
			if  ($parentEventPhase <= PHASE_DOWN) {
				if ($priorEvent->getType() == TYPE_UNICAST_SINGLE_DIRECTIONAL) {
					$result[$type] = $priorEvent;
				}
			} else if ($parentEventPhase >= PHASE_UP) {
				if ($priorEvent->getType() == TYPE_UNICAST_SINGLE_DIRECTIONAL_REVERSE) {
					$result[$type] = $priorEvent;
				}				
			}
		}

		return $result;
	}

	/**
	 * @return void
	 */
	final public function setPriorEvents(array $events)
	{
		foreach ($events as $event) {
			$this->addPriorEvent($event);
		}
	}
	
	/**
	 * @return void
	 */
	final public function flushPriorEvents($phase = null)
	{
		if (null === $phase) {
			$this->priorEvents = [];
			return;
		}
		
		foreach (array_keys($this->getPriorEvents($phase)) as $key) {
			unset($this->priorEvents[$key]);
		}
	}

	/**
	 * @return array
	 */
	final public function mergePriorEvents($parentEvent, ...$listeners)
	{
		$phase = $parentEvent->getPhase();

		foreach ($listeners as $listener) {
			foreach ($listener->getPriorEvents($phase) as $priorEvent) {
				$type = $priorEvent::toType();
				if (isset($this->priorEvents[$type]) &&
					$this->priorEvents[$type] instanceof Type\MergeableInterface)
				{
					$this->priorEvents[$type]->merge($priorEvent);
				} else {
					$this->priorEvents[$type] = $priorEvent;
				}

				$listener->flushPriorEvents($phase);
			}
		}
	}
}
