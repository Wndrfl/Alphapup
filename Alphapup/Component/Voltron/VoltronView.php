<?php
namespace Alphapup\Component\Voltron;

class VoltronView implements \ArrayAccess
{
	private
		$_attributes=array(),
		$_children=array(),
		$_parent,
		$_types=array();
	
	public function attribute($var)
	{
		return (isset($this->_attributes[$var])) ? $this->_attributes[$var] : false;
	}
	
	public function attributes()
	{
		return $this->_attributes;
	}
	
	public function child($name)
	{
		return (isset($this->_children[$name])) ? $this->_children[$name] : false;
	}
	
	public function children()
	{
		return $this->_children;
	}
	
	public function hasParent()
	{
		return (!empty($this->_parent)) ? true : false;
	}
	
	public function offsetExists($offset)
	{
		return (isset($this->_children[$offset])) ? true : false;
	}

	public function offsetGet($offset)
	{
		return (isset($this->_children[$offset])) ? $this->_children[$offset] : false;
	}
	
	public function offsetSet($offset,$value)
	{
		return $this->_children[$offset] = $value;
	}
	
	public function offsetUnset($offset)
	{
		unset($this->_children[$offset]);
	}
	
	public function parent()
	{
		return $this->_parent;
	}
		
	public function setAttribute($var,$val)
	{
		$this->_attributes[$var] = $val;
		return $this;
	}
	
	public function setAttributes($attributes=array())
	{
		foreach($attributes as $key => $val) {
			$this->setAttribute($key,$val);
		}
		return $this;
	}
	
	public function setChild($name,VoltronView $child)
	{
		$this->_children[$name] = $child;
		return $this;
	}
	
	public function setParent(VoltronView $parent=null)
	{
		$this->_parent = $parent;
		return $this;
	}
	
	public function setTypes(array $types=array())
	{
		$this->_types = $types;
	}
	
	public function types()
	{
		return $this->_types;
	}
}