<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type;

use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\_Trait\CreateObject;

require_once __DIR__ . '/../Manager.php';

abstract class AbstractEvent
{
	use CreateObject;
	
	private $status;
	private $exception;
	private $stopPropagationFlag = false;
	private $lastNode;
	private $autoTriggerEvent;
	private $lastDispatchedEvent;
	private $terminateNodeHandler;
	private $target;
	private $originalTarget;
	private $currentNode;	
	private $result = array();

	protected $resultType;
	protected $phase;
	protected $type;
	
	/**
	 * If set to true stops event propagation once one of its listeners invoked
	 * and its returned value is not null
	 * 
	 * @var boolean
	 */
	protected $singleResultFlag = false;

	public function __construct()
	{
		$this->status = Event\STATUS_NONE;
		$this->phase = Event\PHASE_INIT;
		$this->resultType = Event\RESULT_TYPE_ALLOW_OVERWRITE;
	}
	
	final public function getType()
	{
		return $this->type;
	}
	
	/**
	 * @return integer
	 */
	final public function getResultType()
	{
		return $this->resultType;
	}
	
	/**
	 * @return string
	 */
	public static function toType()
	{
		return static::class;
	}
	
	/**
	 * @return mixed
	 */
	final public function getResult()
	{
		switch ($this->resultType) {
			case Event\RESULT_TYPE_ALLOW_OVERWRITE:
			case Event\RESULT_TYPE_SINGLE:				
				$result = isset($this->result[0]) ? $this->result[0] : null;
				if ($result instanceof \Closure) {
					$result = $result();
				}
				break;
				
			case Event\RESULT_TYPE_SINGLE_CLOSURE:
				$result = ( isset($this->result[0]) &&
					$this->result[0] instanceof \Closure )
						? $this->result[0] : null;
				break;
			
			case Event\RESULT_TYPE_STACKED:
				$result = $this->result;
				break;
		}
		
		return $result;
	}
	
	/**
	 * @return boolean
	 */
	final public function hasResult()
	{
		return count($this->result) ? true : false;
	}
	
	/**
	 * @return void
	 */
	final public function setResult($value)
	{
		if (null === $value) {
			return;
		}
		
		switch ($this->resultType) {
			case Event\RESULT_TYPE_ALLOW_OVERWRITE:
				$this->result[0] = $value;
				break;
			
			case Event\RESULT_TYPE_SINGLE:
				if ( ! count($this->result)) {
					$this->result[0] = $value;
				}
				break;
			
			case Event\RESULT_TYPE_SINGLE_CLOSURE:
				if ( ! count($this->result)) {
					$this->result[0] = $value;
				}
				break;
			
			case Event\RESULT_TYPE_STACKED:
				$this->result[] = $value;
				break;
		}
	}
	
	public function getTarget()
	{
		return $this->target;
	}

	/**
	 * 
	 */
	public function setTarget($target)
	{
		$this->target = $target;
	}

	/**
	 * 
	 */
	public function getOriginalTarget()
	{
		return $this->originalTarget;
	}

	/**
	 * @return null
	 */
	public function setOriginalTarget($origin)
	{
		$this->originalTarget = $origin;
	}

	/**
	 * @return boolean
	 */
	final public function getStatus()
	{
		return $this->status;
	}
	
	/**
	 * @return $this
	 */
	final public function setStatus($status)
	{
		$this->status = $status;
		
		return $this;
	}
	
	/**
	 * @return integer
	 */
	final function getPhase()
	{
		return $this->phase;
	}
	
	/**
	 * @return $this
	 */
	final function setPhase($phase)
	{
		$this->phase = $phase;
		
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	final public function getStopPropagationFlag()
	{
		return $this->stopPropagationFlag;
	}
	
	/**
	 * @return null
	 */
	final public function resetStopPropagationFlag()
	{
		$this->stopPropagationFlag = false;
	}
	
	/**
	 * @return $this
	 */
	final public function stopPropagation()
	{
		$this->stopPropagationFlag = true;
		
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	final public function getSingleResultFlag()
	{
		return $this->singleResultFlag;
	}
	
	/**
	 *
	 */
	final public function getLastNode()
	{
		return $this->lastNode;
	}
	
	/**
	 * @return null
	 */
	final public function setLastNode($node)
	{
		$this->lastNode = $node;
	}
	
	/**
	 * @return null
	 */
	final public function discard()
	{
		$this->status = Event\STATUS_DISCARDED;
	}
	
	final public function getException()
	{
		return $this->exception;
	}
	
	/**
	 * @return $this
	 */
	final public function setException($exception)
	{
		$this->exception = $exception;
		
		return $this;
	}
	
	/**
	 * @return $this
	 */
	public function setAutoTriggerEvent($event)
	{
		$this->autoTriggerEvent = $event;
		
		return $this;
	}
	
	
	public function getAutoTriggerEvent()
	{
		return $this->autoTriggerEvent;
	}
	
	public function hasAutoTriggerEvent()
	{
		return $this->autoTriggerEvent ? true : false;
	}

	public function getLastDispatchedEvent()
	{
		return $this->lastDispatchedEvent;
	}

	public function setLastDispatchedEvent($event)
	{
		$this->lastDispatchedEvent = $event;
		
		return $this;
	}
	
	/**
	 * @return \Closure
	 */
	final public function getTerminateNodeHandler()
	{
		return $this->terminateNodeHandler;
	}
	
	/**
	 * @return null
	 */
	final public function setTerminateNodeHandler(\Closure $handler)
	{
		$this->terminateNodeHandler = $handler;
	}
	
	
	final public function getCurrentNode()
	{
		return $this->currentNode;
	}
	
	/**
	 * 
	 */
	final public function setCurrentNode($node)
	{
		$this->currentNode = $node;
	}

	/**
	 * Returns true if current node is a package.
	 * 
	 * @return boolean
	 */
	final public function isCurrentNodePackage()
	{
		return $this->getCurrentNode() instanceof
			\PHPCrystal\PHPCrystal\Component\Package\AbstractPackage ? true : false;				
	}
	
	//
	// Event hooks
	//
		
	/**
	 * @return null
	 */
	public function onPreDispatch() {  }
	
	/**
	 * @return null
	 */
	public function onPostDispatch() {  }
}
