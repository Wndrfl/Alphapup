<?php
namespace Alphapup\Component\NitPick\Rule;

use Alphapup\Component\NitPick\Rule\BaseRule;

class IsEqualTo extends BaseRule
{
	public function __construct()
	{
		parent::__construct('isEqualTo');
	}
	
	public function defaultMessage()
	{
		return sprintf('This value must be equal to %s.',$this->input()->label(),$this->_compareNumber);
	}
	
	// requires $options['number']
	public function test($value,$options=array())
	{
		if(!is_numeric($value)) {
			return false;
		}
		return ($value == $options['number']) ? true : false;
	}
}