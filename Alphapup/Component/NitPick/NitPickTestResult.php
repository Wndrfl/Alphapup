<?php
namespace Alphapup\Component\NitPick;

class NitPickTestResult
{
	private
		$_message,
		$_success,
		$_testedValue;
		
	public function __construct($success,$message,$testedValue)
	{
		$this->setSuccess($success);
		$this->setMessage($message);
		$this->setTestedValue($testedValue);
	}
	
	public function message()
	{
		return $this->_message;
	}
	
	public function setMessage($message)
	{
		$this->_message = $message;
	}
	
	public function setSuccess($success)
	{
		$this->_success = (bool) $success;
	}
	
	public function setTestedValue($testedValue)
	{
		$this->_testedValue = $testedValue;
	}
	
	public function success()
	{
		return $this->_success;
	}
	
	public function testedValue()
	{
		return $this->_testedValue;
	}
}