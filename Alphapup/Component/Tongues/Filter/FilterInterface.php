<?php
namespace Alphapup\Component\Tongues\Filter;

use Alphapup\Component\Tongues\Filter\FilterMatch;
use Alphapup\Component\Tongues\TongueString;

interface FilterInterface
{
	public function filter(TongueString $string,FilterMatch $match);
	public function name();
}