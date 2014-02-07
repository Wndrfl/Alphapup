<?php
namespace Alphapup\Component\Fetch\SQL\Expr;

class Join
{
	const
		TYPE_INNER 			= 'INNER',
		TYPE_LEFT 			= 'LEFT';
		
	const
		CONDITION_TYPE_ON 	= 'ON',
		CONDITION_TYPE_WITH	= 'WITH';
		
	private
		$_alias,
		$_conditions = array(),
		$_conditionType,
		$_table,
		$_type;
		
	public function __construct($type,$table,$alias,$conditionType,$conditions=array())
	{
		$this->_type = $type;
		$this->_table = $table;
		$this->_alias = $alias;
		$this->_conditions = $conditions;
		$this->_conditionType = $conditionType;
	}
	
	public function alias()
	{
		return $this->_alias;
	}
	
	public function conditions()
	{
		return $this->_conditions;
	}
	
	public function conditionType()
	{
		return $this->_conditionType;
	}
	
	public function table()
	{
		return $this->_table;
	}
	
	public function type()
	{
		return $this->_type;
	}
	
	public function __toString()
	{
		if(!is_array($this->_conditions)) {
			$this->_conditions = array($this->_conditions);
		}
		
		$sql = $this->_type.' JOIN';
		$sql .= ' '.$this->_table;
		if(!is_null($this->_alias)) {
			$sql .= ' '.$this->_alias;
		}
		if($this->_conditions) {
			$sql .= ' '.strtoupper($this->_conditionType).' '.implode(', ',$this->_conditions);
		}
		return $sql;
	}
}