<?php
namespace Alphapup\Component\Carto\CQL\Part;

use Alphapup\Component\Carto\CQL\Part\PropertyExpression;

class OrderExpression
{
	private
		$_direction,
		$_property;
		
	public function __construct(PropertyExpression $property,$direction='ASC')
	{
		$this->_property = $property;
		$this->_direction = $direction;
	}

	public function direction()
	{
		return $this->_direction;
	}
	
	public function property()
	{
		return $this->_property;
	}
	
	public function translate($translator)
	{
		return $translator->translateOrderExpression($this);
	}
}