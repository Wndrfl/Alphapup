<?php
namespace Alphapup\Component\NitPick\Rule;

use Alphapup\Component\NitPick\Rule\BaseRule;

class IsShorterThan extends BaseRule
{
	public function __construct()
	{
		parent::__construct('isShorterThan');
	}
	
	public function test($value,$options=array())
	{
		return (strlen($value) < $options['number']) ? true : false;
	}
}