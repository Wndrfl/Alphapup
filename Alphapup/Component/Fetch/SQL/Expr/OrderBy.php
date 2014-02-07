<?php
namespace Alphapup\Component\Fetch\SQL\Expr;

class OrderBy
{
	private
		$_column = null,
		$_direction = null;
		
	public function __construct($column,$direction='ASC')
	{
		$this->_column = $column;
		$this->_direction = $direction;
	}
	
	public function column()
	{
		return $this->_column;
	}
	
	public function direction()
	{
		return $this->_direction;
	}
	
	public function __toString()
	{
		$sql = $this->_column.' '.$this->_direction;
		return $sql;
	}
}