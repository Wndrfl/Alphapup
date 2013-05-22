<?php
namespace Alphapup\Component\Carto;

class ArrayCollection implements \Iterator, \ArrayAccess
{
	private
		$_values = array(),
		$_pointer = 0;
	
	public function __construct($values=array())
	{
		if(is_array($values)) {
			$this->setValues($values);
		}else{
			$this->setValue($values);
		}
	}
	
	public function clear()
	{
		$this->_values = array();
		$this->rewind();
	}
	
    public function current()
	{
        return $this->_values[$this->_pointer];
    }

	public function isEmpty()
	{
		return !$this->_values;
	}

    public function key()
	{
        return $this->_pointer;
    }
	
	public function offsetExists($offset)
	{
		return (isset($this->_values[$offset])) ? true : false;
	}
	
	public function offsetGet($offset)
	{
		return $this->_values[$offset];
	}
	
	public function offsetSet($offset,$value)
	{
		$this->_values[$offset] = $value;
	}
	
	public function offsetUnset($offset)
	{
		unset($this->_values[$offset]);
	}

    public function next()
	{
        ++$this->_pointer;
    }
	
	public function rewind()
	{
        $this->_pointer = 0;
    }

	public function setValue($key=null,$value)
	{
		if(is_null($key)) {
			array_push($this->_values,$value);
		}else{
			$this->_values[$key] = $value;
		}
	}

	public function setValues(array $values=array())
	{
		foreach($values as $key => $value) {
			$this->setValue(null,$value);
		}
	}
	
    public function valid()
	{
        return isset($this->_values[$this->_pointer]);
    }

	public function values()
	{
		return $this->_values;
	}
}