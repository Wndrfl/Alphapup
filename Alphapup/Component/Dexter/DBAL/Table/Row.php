<?php
namespace Alphapup\Component\Dexter\DBAL\Table;

class Row implements \ArrayAccess,\Iterator
{
	private 
		$_fields = array(),
		$_pointer = 0;
	
	public function __get($k)
	{
		return $this->get($k);
	}
	
	public function current()
	{
        return $this->_fields[$this->_pointer];
    }
	
	public function get($k)
	{
		return (isset($this->_fields[$k])) ? $this->_fields[$k] : false;
	}
	
	public function import($data=array())
	{
		foreach($data as $k => $v) {
			$this->_fields[$k] = $v;
		}
	}
	
	public function key()
	{
        return $this->_pointer;
    }

	public function next()
	{
        ++$this->_pointer;
    }
	
	public function offsetExists($offset)
	{
		return (isset($this->_fields[$offset])) ? true : false;
	}

	public function offsetGet($offset)
	{
		return (isset($this->_fields[$offset])) ? $this->_fields[$offset] : false;
	}
	
	public function offsetSet($offset,$value)
	{
		return $this->_fields[$offset] = $value;
	}
	
	public function offsetUnset($offset)
	{
		unset($this->_fields[$offset]);
	}
	
	public function rewind()
	{
        $this->_pointer = 0;
    }
	
	public function toArray()
	{
		$fields = array();
		foreach($this->_fields as $k => $v) {
			$fields[$k] = $v;
		}
		return $fields;
	}
	
	public function valid()
	{
        return isset($this->_fields[$this->pointer]);
    }
}