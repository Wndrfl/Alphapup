<?php
namespace Alphapup\Component\NitPick\Rule;

use Alphapup\Component\NitPick\Rule\BaseRule;

class IsLongerThan extends BaseRule
{
	public function __construct()
	{
		parent::__construct('isLongerThan');
	}
	
	public function defaultMessage()
	{
		return sprintf('The value of the %s field must be longer than %s characters.',$this->input()->label(),$this->_compareNumber);
	}
	
	public function test($value,$options=array())
	{
		return (strlen($value) > $options['number']) ? true : false;
	}
}