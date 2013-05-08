<?php
namespace Alphapup\Component\Voltron\Type;

use Alphapup\Component\Voltron\Type\BaseType;

class File extends BaseType
{
	public function name()
	{
		return 'file';
	}
	
	public function parent()
	{
		return 'text';
	}
}