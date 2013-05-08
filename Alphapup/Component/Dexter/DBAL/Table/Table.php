<?php
namespace Alphapup\Component\Dexter\DBAL\Table;

use Alphapup\Component\Dexter\DBAL\Table\Column;

class Table
{
	private
		$_columns = array(),
		$_name,
		$_primary;
		
	public function __construct($name,$schema=array())
	{
		$this->_setName($name);
		foreach($schema as $column) {
			$this->_setColumn($column['Field'],$column);
		}
	}
	
	private function _setColumn($name,$schema)
	{
		$this->_columns[$name] = new Column($schema);
	}
	
	private function _setName($name)
	{
		$this->_name = $name;
	}
	
	public function column($column)
	{
		return (isset($this->_columns[$column])) ? $this->_columns[$column] : false;
	}
	
	public function name()
	{
		return $this->_name;
	}
	
	public function primary()
	{
		if(!empty($this->_primary)) {
			return $this->_primary;
		}
		$primary = array();
		foreach($this->_columns as $column) {
			if($column->is_primary()) {
				$primary[] = $column;
			}
		}
		if(count($primary) == 0) {
			// DO EXCEPTION
		}
		$this->_primary = $primary;
		return $this->_primary;
	}
}