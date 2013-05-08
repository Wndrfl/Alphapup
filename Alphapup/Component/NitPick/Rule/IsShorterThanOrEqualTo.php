<?php
namespace Alphapup\Component\NitPick\Rule;

use Alphapup\Component\NitPick\Rule\BaseRule;

class IsShorterThanOrEqualTo extends BaseRule
{
	public function __construct()
	{
		parent::__construct('isShorterThanOrEqualTo');
	}
	
	public function test($value,$options=array())
	{
		return (strlen($value) <= $options['number']) ? true : false;
	}
}