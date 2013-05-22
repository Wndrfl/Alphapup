<?php
namespace Alphapup\Component\Carto\CQL;

class Strategy
{	
	const
		SIMPLE		= 1,
		QUALIFIER 	= 2;
	
	private
		$_conditionalAssociations = array(),
		$_qualifyingEntities = array(),
		$_type;
		
	public function __construct()
	{
		$this->_type = self::SIMPLE;
	}
	
	public function addQualifyingEntity($alias,$name)
	{
		$this->_qualifyingEntities[$alias] = $name;
		return $this;
	}
	
	public function conditionalAssociations()
	{
		return $this->_conditionalAssociations;
	}
	
	public function qualifyingEntities()
	{
		return $this->_qualifyingEntities;
	}
	
	public function setConditionalAssociations(array $conditionalAssociations=array()) {
		$this->_conditionalAssociations = $conditionalAssociations;
		return $this;
	}
	
	public function setType($type)
	{
		switch($type) {
			case self::SIMPLE:
				$this->_type = self::SIMPLE;
				break;
			
			case self::QUALIFIER:
				$this->_type = self::QUALIFIER;
				break;
			
			default:
				// DO EXCEPTION;
				break;
		}
		
		return $this;
	}
	
	public function type()
	{
		return $this->_type;
	}
}