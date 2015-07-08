<?php
namespace PHPCrystal\PHPCrystal\Component\Package\Heap;

class MinHeap extends \SplHeap
{
	/**
	 * @return integer
	 */
	protected function compare($ext1, $ext2)
	{
		$priority1 = $ext1->getPriority();
		$priority2 = $ext2->getPriority();
        
		if ($priority1 === $priority2) {
			return 0;
		}
        
		return $priority1 < $priority2 ? 1 : -1; 
	}
}
