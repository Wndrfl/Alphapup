<?php
namespace Alphapup\Component\NitPick\Rule;

use Alphapup\Component\NitPick\Rule\BaseRule;

class IsNumeric extends BaseRule
{
	public function __construct()
	{
		parent::__construct('isNumeric');
	}
	
	public function test($value,$options=array())
	{
		return preg_match("#([^0-9]+)#",$value) ? false : true;
	}
}