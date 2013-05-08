<?php
namespace Alphapup\Component\NitPick\Rule;

use Alphapup\Component\NitPick\Rule\BaseRule;

class IsAlphaDash extends BaseRule
{
	public function __construct()
	{
		parent::__construct('isAlphaDash');
	}
	
	public function test($value,$options=array())
	{
		return preg_match("#([^A-Za-z_-]+)#",$value) ? false : true;
	}
}