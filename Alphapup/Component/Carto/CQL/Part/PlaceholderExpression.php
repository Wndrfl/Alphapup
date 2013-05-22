<?php
namespace Alphapup\Component\Carto\CQL\Part;

class PlaceholderExpression
{
	private
		$_placeholder,
		$_value;
		
	public function __construct($placeholder,$value)
	{
		$this->_placeholder = $placeholder;
		$this->_value = $value;
	}
	
	public function placeholder()
	{
		return $this->_placeholder;
	}
	
	public function translate($translator)
	{
		return $translator->translatePlaceholderExpression($this);
	}
	
	public function value()
	{
		return $this->_value;
	}
}