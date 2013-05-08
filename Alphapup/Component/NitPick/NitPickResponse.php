<?php
namespace Alphapup\Component\NitPick;

use Alphapup\Component\NitPick\NitPickTestResult;

class NitPickResponse
{
	private
		$_errors = array(),
		$_validated = array();
		
	public function __construct($results=array())
	{
		$this->setResults($results);
	}
	
	public function error($num)
	{
		if(!isset($this->_errors[$num])) {
			return false;
		}
		$result = $this->_errors[$num];
		return $result->message();
	}
	
	public function errors()
	{
		return $this->_errors;
	}
	
	public function firstError()
	{
		return $this->error(0);
	}
	
	public function isValid()
	{
		return (count($this->_errors) > 0) ? false : true;
	}
		
	public function setError(NitPickTestResult $result)
	{
		$this->_errors[] = $result;
	}
	
	public function setResult(NitPickTestResult $result)
	{
		if($result->success()) {
			$this->setValidated($result);
		}else{
			$this->setError($result);
		}
	}
	
	public function setResults($results=array())
	{
		foreach($results as $result) {
			$this->setResult($result);
		}
	}
	
	public function setValidated(NitPickTestResult $result)
	{
		$this->_validated[] = $result;
	}
	
	public function totalErrors()
	{
		return count($this->_errors);
	}
}