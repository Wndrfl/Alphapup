<?php
namespace Alphapup\Component\Carto\CQL\Part;

class OperatorExpression
{
	const
		O_EQUAL_TO 					= 1,
		O_GREATER_THAN 				= 2,
		O_GREATER_THAN_OR_EQUAL_TO  = 3,
		O_LESS_THAN					= 4,
		O_LESS_THAN_OR_EQUAL_TO		= 5,
		O_NOT_EQUAL_TO				= 6;
		
	private
		$_type;
		
	public function __construct($type)
	{
		$this->_type = $type;
	}
	
	public function translate($translator)
	{
		return $translator->translateOperatorExpression($this);
	}

	public function type()
	{
		return $this->_type;
	}
}