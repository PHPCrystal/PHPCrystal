<?php
namespace PHPCrystal\PHPCrystal\Component\Console\Input;

use Symfony\Component\Console\Input as Input;
use PHPCrystal\PHPCrystal\_Trait\CreateObject;

class Argument extends Input\InputArgument
{
	use CreateObject;
}
