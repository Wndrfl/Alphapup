<?php
namespace Alphapup\Component\Tongues\Filter;

use Alphapup\Component\Tongues\Filter\BaseFilter;
use Alphapup\Component\Tongues\TongueString;

class Possessive extends BaseFilter
{
	private
		$_name = 'possessive';
	
	public function filter(TongueString $string,FilterMatch $match)
	{
		$content = $match->content();
		$ct = $match->closingTagIndex();
		$m1 = strtolower($string->charAt($ct-1));
		
		if($m1 == 's') {
			$replace = $content.'\'';
		}else{
			$replace = $content.'\'s';
		}
		
		$this->setReplacement($match,$string,$replace);
	}
	
	public function name()
	{
		return $this->_name;
	}
}