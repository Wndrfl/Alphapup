<?php
namespace Alphapup\Component\NitPick\Rule;

use Alphapup\Component\NitPick\Rule\BaseRule;

class IsPattern extends BaseRule
{		
	public function __construct()
	{
		parent::__construct('isPattern');
	}
	
	public function test($value,$options=array())
	{
		return preg_match("#".$options['pattern']."#",$value) ? false : true;
	}
}