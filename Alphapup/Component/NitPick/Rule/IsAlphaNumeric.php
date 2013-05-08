<?php
namespace Alphapup\Component\NitPick\Rule;

use Alphapup\Component\NitPick\Rule\BaseRule;

class IsAlphaNumeric extends BaseRule
{
	public function __construct()
	{
		parent::__construct('isAlphaNumeric');
	}
	
	public function test($value,$options=array())
	{
		return preg_match("#([^A-Za-z0-9]+)#",$value) ? false : true;
	}
}