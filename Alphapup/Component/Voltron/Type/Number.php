<?php
namespace Alphapup\Component\Voltron\Type;

use Alphapup\Component\Voltron\Type\BaseType;

class Url extends BaseType
{
	public function name()
	{
		return 'number';
	}
	
	public function parent()
	{
		return 'text';
	}
}