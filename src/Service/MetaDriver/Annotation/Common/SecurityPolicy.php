<?php
namespace PHPCrystal\PHPCrystal\Service\MetaDriver\Annotation\Common;

use PHPCrystal\PHPCrystal\Service\MetaDriver\Annotation\AbstractAnnotation;

use PHPCrystal\PHPCrystal\Contract\EventCatalyst;
use PHPCrystal\PHPCrystal\Service\Event as  Event;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *  @Attribute("authRequired", type="boolean")
 * })
 */
class SecurityPolicy extends AbstractAnnotation implements EventCatalyst
{
	private $authRequired;

	public function __construct(array $values)
	{
		$this->authRequired = (bool)$values['authRequired'];
	}
	
	/**
	 * @return
	 */
	public function getEvent()
	{
		$event =  Event\Type\System\SecurityPolicyApplication::create()
			->setAuthRequired($this->authRequired);
		
		return $event;
	}
}
