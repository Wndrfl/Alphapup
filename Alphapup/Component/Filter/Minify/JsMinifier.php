<?php
namespace Alphapup\Component\Filter\Minify;

use Alphapup\Component\Filter\Minify\MinifierAbstract;

class JsMinifier extends MinifierAbstract
{
	public function name()
	{
		return 'jsMinifier';
	}
	
	public function process()
	{	
		// new
		while($this->pointer() < $this->rawLength()) {
			if($this->processRegex()) { continue;}
			if($this->processSingleLineComment()) { continue;}
			if($this->processMultiLineComment()) { continue;}
			if($this->processEscape()) { continue;}
			if($this->processQuote()) { continue;}
			if($this->currentIsSpace()) {
				if($this->processInString()) { continue;}
				if($this->processBetween('alpha','alpha')) { continue;}
			
				// space is luxury
				$this->skip();
				continue;
			}
			
			// not a comment or space, send to output
			$this->recordAndOnward();
		}
	}
}