<?php
namespace Alphapup\Component\Carto\SQL\Expr;

class Table
{
	private
		$_alias = null,
		$_name = '';
		
	public function __construct($name,$alias=null)
	{
		$this->_alias = $alias;
		$this->_name = $name;
	}
	
	public function alias()
	{
		return $this->_alias;
	}
	
	public function name()
	{
		return $this->_name;
	}
	
	public function __toString()
	{
		$sql = $this->_name;
		if(!is_null($this->_alias) && $this->_alias !== $this->_name) {
			$sql .= ' '.$this->_alias;
		}
		return $sql;
	}
}