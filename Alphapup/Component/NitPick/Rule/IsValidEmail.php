<?php
namespace Alphapup\Component\NitPick\Rule;

use Alphapup\Component\NitPick\Rule\BaseRule;

class IsValidEmail extends BaseRule
{		
	public function __construct()
	{
		parent::__construct('isValidEmail');
	}
	
	public function defaultMessage()
	{
		return 'This value is not a valid email.';
	}
	
	public function test($value,$options=array())
	{	
		return (!filter_var($value,FILTER_VALIDATE_EMAIL)) ? false : true;
	}
}