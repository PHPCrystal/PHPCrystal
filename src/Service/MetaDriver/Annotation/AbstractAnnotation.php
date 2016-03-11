<?php
namespace PHPCrystal\PHPCrystal\Service\MetaDriver\Annotation;


abstract class AbstractAnnotation
{
	/**
	 * @return void
	 */
	public function merge($annot)
	{
       foreach (get_object_vars($annot) as $key => $value) {
            $this->$key = $value;
        }
	}
}