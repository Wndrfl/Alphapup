<?php
namespace Alphapup\Component\Voltron\Type;

use Alphapup\Component\Voltron\Type\BaseType;

class Password extends BaseType
{
	public function name()
	{
		return 'password';
	}
	
	public function parent()
	{
		return 'text';
	}
}