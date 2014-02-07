<?php
namespace Alphapup\Component\Fetch\SQL\Expr;

class Insert
{
	private
		$_columns = array(),
		$_table = null;
		
	public function __construct(array $columns=array(),$table)
	{
		$this->_columns = $columns;
		$this->_table = $table;
	}
	
	public function columns()
	{
		return $this->_columns;
	}
	
	public function table()
	{
		return $this->_table;
	}
	
	public function __toString()
	{	
		$row = 0;
		$multi = false;
		$cols = array();
		$values = array();
		foreach($this->_columns as $key => $val) {
			
			$row++;
			$implode = false;
			if($multi == false) {
				if(is_array($val)) {
					if($row == 1) {
						$multi = true;
					}else{
						$implode = true;
					}
				}	
			}
			
			if(!$multi) {
				$cols[] = $key;
				$values[] = ($implode) ? implode(', ',$val) : $val;
			}else{
				if(!isset($cols[$key])) {
					$cols[$key] = array();
					$values[$key] = array();
				}
				
				foreach($val as $k => $v) {
					$cols[$key][] = $k;
					$values[$key][] = $v;
				}
			}
		}
		
		if(!is_null($this->_offset)) {
			$sql .= intval($this->_offset).', ';
		}
		return $sql.intval($this->_limit);
	}
}