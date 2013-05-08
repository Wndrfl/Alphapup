<?php
namespace Alphapup\Component\Voltron\Type;

use Alphapup\Component\Voltron\Type\BaseType;
use Alphapup\Component\Voltron\VoltronBuilder;
use Alphapup\Component\Voltron\VoltronViewInterface;

class Form extends BaseType
{	
	public function name()
	{
		return 'form';
	}
	
	public function parent()
	{
		return 'field';
	}
}