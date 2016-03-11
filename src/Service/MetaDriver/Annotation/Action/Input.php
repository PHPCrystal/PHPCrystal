<?php
namespace PHPCrystal\PHPCrystal\Service\MetaDriver\Annotation\Action;

use PHPCrystal\PHPCrystal\Service\MetaDriver\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *  @Attribute("class", type="string"),
 *  @Attribute("targetMethod", type="string")
 * })
 */
class Input extends AbstractAnnotation
{

}
