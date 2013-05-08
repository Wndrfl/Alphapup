<?php
namespace Alphapup\Component\Dexter;

use Alphapup\Component\Dexter\Dexter;

class Query
{
	private
		$_error,
		$_params=array(),
		$_results=array(),
		$_rowCount=0,
		$_sql,
		$_startTime,
		$_stopTime,
		$_success=false;
		
	public function __construct(Dexter $dexter,$sql,array $params=array())
	{
		$this->_dexter = $dexter;
		$this->_sql = $sql;
		$this->_params = $params;
	}
	
	public function errorMessage()
	{
		return (!empty($this->_error)) ? $this->_error[2] : false;
	}
	
	public function execute()
	{
		$this->_dexter->query($this);
		return $this;
	}
	
	public function id()
	{
		return $this->_sql.implode('',$this->_params);
	}
	
	public function params()
	{
		return $this->_params;
	}
	
	public function results()
	{
		return $this->_results;
	}
	
	public function rowCount()
	{
		return $this->_rowCount;
	}
	
	public function setError($error)
	{
		$this->_error = $error;
	}
	
	public function setParam($key,$value)
	{
		$this->_params[$key] = $value;
	}
	
	public function setParams(array $params=array())
	{
		foreach($params as $key => $value) {
			$this->setParam($key,$value);
		}
	}

	public function setResults($results=array())
	{
		$this->_results = $results;
	}
	
	public function setRowCount($rowCount)
	{
		$this->_rowCount = $rowCount;
	}
	
	public function setSuccess($bool)
	{
		$this->_success = (bool)$bool;
		return $this;
	}
	
	public function sql()
	{
		return $this->_sql;
	}
	
	public function startTimer()
	{
		$time = microtime();
		$this->_startTime = $time;
	}
	
	public function stopTimer()
	{
		$time = microtime();
		$this->_stopTime = $time;
	}
	
	public function totalTime()
	{
		return ($this->_stopTime - $this->_startTime);
	}
	
	public function wasSuccessful()
	{
		return (empty($this->_success) || $this->_success == false) ? false : true;
	}
}