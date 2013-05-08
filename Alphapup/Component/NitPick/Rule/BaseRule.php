<?php
namespace Alphapup\Component\NitPick\Rule;

use Alphapup\Component\NitPick\Rule\RuleInterface;
use Alphapup\Component\NitPick\NitPickTestResult;

abstract class BaseRule implements RuleInterface
{
	private
		$_genericMessage = 'This value is invalid.',
		$_name,
		$_message;
		
	public function __construct($name)
	{
		$this->setName($name);
	}
	
	public function defaultMessage()
	{
		return false;
	}
	
	public function genericMessage()
	{
		return $this->_genericMessage;
	}
	
	public function hasMessage()
	{
		return (!empty($this->_message)) ? true : false;
	}
	
	public function message()
	{
		if(!empty($this->_message)) {
			return $this->_message;
		}elseif($default = $this->defaultMessage()) {
			return $default;
		}else{
			return $this->genericMessage();
		}
	}
	
	public function name()
	{
		return $this->_name;
	}
	
	public function setMessage($message)
	{
		$this->_message = $message;
	}
	
	public function setName($name)
	{
		$this->_name = $name;
	}
}