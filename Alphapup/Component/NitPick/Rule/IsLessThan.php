<?php
namespace Alphapup\Component\NitPick\Rule;

use Alphapup\Component\NitPick\Rule\BaseRule;

class IsLessThan extends BaseRule
{
	public function __construct()
	{
		parent::__construct('isLessThan');
	}
	
	public function defaultMessage()
	{
		return sprintf('The value of the %s field must be less than the number %s.',$this->input()->label(),$this->_compareNumber);
	}
	
	public function test($value,$options=array())
	{
		if(!is_numeric($value)) {
			return false;
		}
		return ($value < $options['number']) ? true : false;
	}
}