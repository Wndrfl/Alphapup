<?php
namespace Alphapup\Component\Carto\CQL\Part;

use Alphapup\Component\Carto\CQL\Part\EntityExpression;

class PropertyExpression
{
	private
		$_entity,
		$_name;
		
	public function __construct($name,$entity)
	{
		$this->_name = $name;
		$this->_entity = $entity;
	}
	
	public function entity()
	{
		return $this->_entity;
	}
	
	public function name()
	{
		return $this->_name;
	}
	
	public function translate($translator)
	{
		return $translator->translatePropertyExpression($this);
	}
}