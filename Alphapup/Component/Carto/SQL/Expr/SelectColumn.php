<?php
namespace Alphapup\Component\Carto\SQL\Expr;

class SelectColumn
{
	private
		$_alias = null,
		$_column = '';
		
	public function __construct($column,$alias=null)
	{
		$this->_alias = $alias;
		$this->_column = $column;
	}
	
	public function alias()
	{
		return $this->_alias;
	}
	
	public function column()
	{
		return $this->_column;
	}
	
	public function __toString()
	{
		$sql = $this->_column;
		if(!is_null($this->_alias)) {
			$sql .= ' AS '.$this->_alias;
		}
		return $sql;
	}
}