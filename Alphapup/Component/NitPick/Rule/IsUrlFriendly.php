<?php
namespace Alphapup\Component\NitPick\Rule;

use Alphapup\Component\NitPick\Rule\BaseRule;

class IsUrlFriendly extends BaseRule
{
	public function __construct()
	{
		parent::__construct('isUrlFriendly');
	}
	
	public function test($value,$options=array())
	{
		return preg_match("#([^A-Za-z0-9_-\~.]+)#",$value) ? false : true;
	}
}