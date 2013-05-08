<?php
namespace Alphapup\Component\Voltron;

class VoltronTypeManager
{
	private
		$_types = array();
		
	public function __construct($types=array())
	{
		$this->setTypes($types);
	}
	
	public function setType(VoltronTypeInterface $type)
	{
		$this->_types[$type->name()] = $type;
	}
	
	public function setTypes(array $types=array())
	{
		foreach($types as $type) {
			$this->setType($type);
		}
	}
	
	public function type($name)
	{
		if(!isset($this->_types[$name])) {
			// DO EXCEPTION
			return false;
		}
		return $this->_types[$name];
	}
}