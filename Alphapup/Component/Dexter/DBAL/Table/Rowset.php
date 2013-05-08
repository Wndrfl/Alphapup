<?php
namespace Alphapup\Component\Dexter\DBAL\Table;

use Alphapup\Component\Dexter\DBAL\Table\Row;

class Rowset implements \ArrayAccess,\Iterator
{
	private $_pointer = 0;
	private $_rows = array();
	
	public function __construct($data=array())
	{
		$this->import($data);
	}
	
	public function current()
	{
		return $this->row($this->pointer());
	}
	
	public function firstRow()
	{
		return $this->row(0);
	}
	
	public function import($rows=array())
	{
		$rows = (is_array($rows)) ? $rows : array($rows);
		foreach($rows as $data) {
			$data = (is_array($data)) ? $data : array($data);
			
			$row = new Row();
			$row->import($data);
			$this->_rows[] = $row;
		}
	}
	
	public function key()
	{
        return $this->_pointer;
    }
	
	public function next()
	{
		return ++$this->_pointer;
	}
	
	public function offsetExists($offset)
	{
		return (isset($this->_rows[$offset])) ? true : false;
	}

	public function offsetGet($offset)
	{
		return (isset($this->_rows[$offset])) ? $this->_rows[$offset] : false;
	}
	
	public function offsetSet($offset,$value)
	{
		return $this->_rows[$offset] = $value;
	}
	
	public function offsetUnset($offset)
	{
		unset($this->_rows[$offset]);
	}
	
	public function onToNext()
	{
		$this->incrementPointer(1);
	}
	
	public function pointer($num=0)
	{
		return $this->_pointer+$num;
	}
	
	public function previous()
	{
		return $this->row($this->pointer(-1));
	}
	
	public function rewind()
	{
        $this->_pointer = 0;
    }
	
	public function row($index)
	{
		return (isset($this->_rows[$index])) ? $this->_rows[$index] : false;
	}
	
	public function rows($start=0,$stop=null)
	{
		if($start > ($this->totalRows()-1)) {
			return false;
		}
		if(!is_null($stop)) {
			$stop = ($stop < $this->totalRows()) ? $stop : ($this->totalRows=1);
		}else{
			$stop = ($this->totalRows=1);
		}
		$rows = array();
		for($i=$start;$i<=$stop;$i++) {
			if(isset($this->_rows[$i])) {
				$rows[] = $this->_rows[$i];
			}
		}
		return $rows;
	}
	
	public function toArray()
	{
		$results = array();
		foreach($this->_rows as $row) {
			$results[] = $row->toArray();
		}
		return $results;
	}
	
	public function totalRows()
	{
		return count($this->_rows);
	}
	
	public function valid()
	{
        return isset($this->_rows[$this->_pointer]);
    }
}