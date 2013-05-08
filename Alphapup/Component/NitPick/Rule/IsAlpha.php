<?php
namespace Alphapup\Component\NitPick\Rule;

use Alphapup\Component\NitPick\Rule\BaseRule;

class IsAlpha extends BaseRule
{
	public function __construct()
	{
		parent::__construct('isAlpha',array('A-Z','a-z'),'uppercase and lowercase letters');
	}
	
	public function test($value,$options=array())
	{
		return preg_match("#([^A-Za-z]+)#",$value) ? false : true;
	}
}