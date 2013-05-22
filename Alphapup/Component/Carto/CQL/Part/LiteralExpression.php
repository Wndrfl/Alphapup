<?php
namespace Alphapup\Component\Carto\CQL\Part;

class LiteralExpression
{
	const
		L_BOOLEAN = 1,
		L_NUMERIC = 2,
		L_STRING  = 3;
		
	private
		$_type,
		$_value;
		
	public function __construct($type,$value)
	{
		$this->_type = $type;
		$this->_value = $value;
	}
	
	public function translate($translator)
	{
		return $translator->translateLiteralExpression($this);
	}

	public function type()
	{
		return $this->_type;
	}

	public function value()
	{
		return $this->_value;
	}
}