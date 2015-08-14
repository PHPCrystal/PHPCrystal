<?php
namespace PHPCrystal\PHPCrystal\Service\Event\Type;

interface MergeableInterface
{
	public function merge($event);
}