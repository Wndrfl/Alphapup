<?php
namespace Alphapup\Component\Tongues\Filter;

use Alphapup\Component\Tongues\Filter\FilterInterface;
use Alphapup\Component\Tongues\Filter\FilterMatch;
use Alphapup\Component\Tongues\TongueString;

abstract class BaseFilter implements FilterInterface
{
	public function setReplacement(FilterMatch $match,TongueString $string,$replace)
	{
		$string->setPointer($match->fullMatchIndex());
		$string->setReplacer($match->fullMatchIndex(),$match->fullMatchIndex()+$match->fullMatchLength(),$replace);
	}
}