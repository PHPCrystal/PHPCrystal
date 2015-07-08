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
	 * @var \PHPCrystal\PHPCrystal\Service\Event\Type\AbstractEvent
	 */
	private $currentEvent;
	private $propagationPath = array();

	protected $parentNode;
	protected $childNodes = array();

	public function __construct()
	{
		$this->addToPropagationPath($this);
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
	 * 
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
	final public function getPropagationPath()
	{
		return $this->propagationPath;
	}
	
	final public function getPropagationPathPrevNode($offset)
	{
		$prev = null;
		
		foreach ($this->getPropagationPath() as $node) {
			if ($node === $offset) {
				return $prev;
			}
			$prev = $node;
		}
	}
	
	/**
	 * @return array
	 */
	final function getPropagationSubPath($offset)
	{
		$result = array();
		
		foreach ($this->getPropagationPath() as $node) {
			$result[] = $node;
			if ($offset === $node) {
				break;
			}
		}
		
		return $result;
	}
	
	/**
	 * @return $this
	 */
	final function addToPropagationPath($node)
	{
		$this->propagationPath[] = $node;
		
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
		$path = $this->getPropagationPath();
		
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
}
