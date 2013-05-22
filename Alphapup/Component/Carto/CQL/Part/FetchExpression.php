<?php
namespace Alphapup\Component\Carto\CQL\Part;

class FetchExpression
{
	private
		$_alias,
		$_name;
		
	public function __construct($name,$alias)
	{
		$this->_alias = $alias;
		$this->_name = $name;
	}
	
	public function alias()
	{
		return (!is_null($this->_alias)) ? $this->_alias : $this->_name;
	}
	
	public function name()
	{
		return $this->_name;
	}
	
	public function translate($translator)
	{
		return $translator->translateFetchExpression($this);
	}
}