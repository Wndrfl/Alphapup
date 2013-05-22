<?php
namespace Alphapup\Component\Carto\CQL\Part;

class WhereClause
{
	private
		$_conditions=array();
		
	public function __construct(array $conditions=array())
	{
		$this->_conditions = $conditions;
	}
	
	public function conditions()
	{
		return $this->_conditions;
	}
	
	public function translate($translator)
	{
		return $translator->translateWhereClause($this);
	}
}