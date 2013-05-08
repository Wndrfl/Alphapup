<?php
namespace Alphapup\Component\NitPick\Rule;

use Alphapup\Component\NitPick\Rule\BaseRule;

class IsGreaterThan extends BaseRule
{
	public function __construct()
	{
		parent::__construct('isGreaterThan');
	}
	
	public function defaultMessage()
	{
		return sprintf('The value of the %s field must be greater than %s.',$this->input()->label(),$this->_compareNumber);
	}
	
	public function test($value,$options=array())
	{
		if(!is_numeric($value)) {
			return false;
		}
		return ($value > $options['number']) ? true : false;
	}
}