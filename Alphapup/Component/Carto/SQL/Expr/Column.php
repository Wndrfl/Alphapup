<?php
namespace Alphapup\Component\Carto\SQL\Expr;

class Column
{
	private
		$_name = '',
		$_table = null;
		
	public function __construct($table,$name)
	{
		$this->_name = $name;
		$this->_table = $table;
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
		return $sql;
	}
}