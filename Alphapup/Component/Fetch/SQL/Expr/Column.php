<?php
namespace Alphapup\Component\Fetch\SQL\Expr;

class Column
{
	private
		$_alias = null,
		$_name = '',
		$_table = null;
		
	public function __construct($table,$name,$alias=null)
	{
		$this->_name = $name;
		$this->_table = $table;
		$this->_alias = $alias;
	}
	
	public function alias()
	{
		return $this->_alias;
	}
	
	public function name()
	{
		return $this->_name;
	}
	
	public function table()
	{
		return $this->_table;
	}
	
	public function __toString()
	{
		$sql = '';
		if(!empty($this->_table)) {
			$sql .= $this->_table.'.';
		}
		$sql .= $this->_name;
		if(!is_null($this->_alias)) {
			$sql .= ' '.$this->_alias;
		}
		return $sql;
	}
}