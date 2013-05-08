<?php
namespace Alphapup\Component\Dexter\DBAL\Table;

class Column
{
	private
		$_default,
		$_extra,
		$_field,
		$_key,
		$_null,
		$_type;
		
	public function __construct($schema=array())
	{
		$this->_setDefault($schema['Default']);
		$this->_setExtra($schema['Extra']);
		$this->_setField($schema['Field']);
		$this->_setKey($schema['Key']);
		$this->_setNull($schema['Null']);
		$this->_setType($schema['Type']);
	}
	
	public function defaultValue()
	{
		return $this->_default;
	}
	
	public function extra()
	{
		return $this->_extra;
	}
	
	public function name()
	{
		return $this->_field;
	}
	
	public function is_primary()
	{
		return ($this->_key == 'PRI');
	}
	
	public function key()
	{
		return $this->_key;
	}

	public function null()
	{
		return $this->_null;
	}
	
	private function _setDefault($default)
	{
		$this->_default = $default;
	}
	
	private function _setExtra($extra)
	{
		$this->_extra = $extra;
	}
	
	private function _setField($field)
	{
		$this->_field = $field;
	}
	
	private function _setKey($key)
	{
		$this->_key = $key;
	}
	
	private function _setNull($null)
	{
		$this->_null = $null;
	}
	
	private function _setType($type)
	{
		$this->_type = $type;
	}
	
	public function type()
	{
		return $this->_type;
	}
}