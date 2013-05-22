<?php
namespace Alphapup\Component\Carto\CQL;

class ParseResult
{
	private
		$_components,
		$_stmt;
		
	public function __construct($stmt,$components=array())
	{
		$this->_stmt = $stmt;
		$this->_components = $components;
	}
	
	public function components()
	{
		return $this->_components;	
	}
	
	public function stmt()
	{
		return $this->_stmt;
	}
}