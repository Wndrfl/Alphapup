<?php
namespace Alphapup\Component\Filter;

use Alphapup\Component\Filter\FilterInterface;

class Hash implements FilterInterface
{
	public function filter($value)
	{
		return str_pad(base_convert(
			sprintf('%u',crc32($value)),10,36),7,'0',STR_PAD_LEFT);
	}
	
	public function name()
	{
		return 'hash';
	}
}