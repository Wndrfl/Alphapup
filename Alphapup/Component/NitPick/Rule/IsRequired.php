<?php
namespace Alphapup\Component\NitPick\Rule;

use Alphapup\Component\NitPick\Rule\BaseRule;

class IsRequired extends BaseRule
{		
	public function __construct()
	{
		parent::__construct('isRequired');
	}
	
	public function defaultMessage()
	{
		return 'This value is required.';
	}
	
	public function test($value,$options=array())
	{	
		if(is_array($value)) {
			if(count($value)>0) {
				foreach($value as $k => $v) {
					if(!$v || is_null($v) || empty($v) || $v == '') {
						return false;
					}
				}
				return true;
			}
			return false;
			
		}elseif(is_object($value)) {
			return true;
			
		}else{
			$value = trim($value);
			return (!$value || is_null($value) || empty($value) || $value == '') ? false : true;
		}
	}
}