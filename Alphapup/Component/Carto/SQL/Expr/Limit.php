<?php
namespace Alphapup\Component\Carto\SQL\Expr;

class Limit
{
	private
		$_limit = null,
		$_offset = null;
		
	public function __construct($limit,$offset=null)
	{
		$this->_limit = $limit;
		$this->_offset = $offset;
	}
	
	public function limit()
	{
		return $this->_limit;
	}
	
	public function offset()
	{
		return $this->_offset;
	}
	
	public function __toString()
	{
		$sql = '';
		if(!is_null($this->_offset)) {
			$sql .= intval($this->_offset).', ';
		}
		return $sql.intval($this->_limit);
	}
}