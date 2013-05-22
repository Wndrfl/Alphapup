<?php
namespace Alphapup\Component\Carto\CQL\Part;

class EntityIdentifierDeclaration
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
		return $this->_alias;
	}
	
	public function name()
	{
		return $this->_name;
	}
	
	public function translate($translator)
	{
		return $translator->translateEntityIdentifierDeclaration($this);
	}
}