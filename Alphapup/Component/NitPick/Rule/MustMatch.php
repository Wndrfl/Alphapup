<?php
namespace Alphapup\Component\NitPick\Rule;

use Alphapup\Component\NitPick\Rule\BaseRule;

class MustMatch extends BaseRule
{		
	public function __construct()
	{
		parent::__construct('mustMatch');
	}
	
	public function test($value,$options=array())
	{
		return ($value == $options['compare']) ? true : false;
	}
}