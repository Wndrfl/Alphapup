<?php
namespace Alphapup\Component\Voltron\Type;

use Alphapup\Component\Voltron\Type\BaseType;

class Email extends BaseType
{
	public function name()
	{
		return 'email';
	}
	
	public function parent()
	{
		return 'text';
	}
}