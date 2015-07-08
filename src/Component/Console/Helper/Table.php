<?php
namespace PHPCrystal\PHPCrystal\Component\Console\Helper;

use Symfony\Component\Console\Helper as Helper;
use PHPCrystal\PHPCrystal\_Trait\CreateObject;

class Table extends Helper\Table
{
	use CreateObject;
}
