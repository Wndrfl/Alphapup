<?php
namespace Alphapup\Component\Carto\CQL\Part;

class AssociatedIdentifierDeclaration
{
	private
		$_alias,
		$_isOptional,
		$_parentAlias,
		$_propertyName;
		
	public function __construct($parentAlias,$propertyName,$alias,$isOptional=false)
	{
		$this->_alias = $alias;
		$this->_parentAlias = $parentAlias;
		$this->_propertyName = $propertyName;
		$this->_isOptional = (bool)$isOptional;
	}
	
	public function alias()
	{
		return $this->_alias;
	}
	
	public function isOptional()
	{
		return $this->_isOptional;
	}

	public function propertyName()
	{
		return $this->_propertyName;
	}

	public function parentAlias()
	{
		return $this->_parentAlias;
	}
	
	public function translate($translator)
	{
		return $translator->translateAssociatedIdentifierDeclaration($this);
	}
}