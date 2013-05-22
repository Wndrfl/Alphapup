<?php
namespace Alphapup\Component\Carto\CQL\Part;

abstract class ConditionExpression
{
	private
		$_bool;
		
	public function __construct($bool=true)
	{
		$this->_bool = $bool;
	}
	
	public function bool()
	{
		return $this->_bool;
	}
}