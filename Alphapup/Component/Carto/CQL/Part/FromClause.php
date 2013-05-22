<?php
namespace Alphapup\Component\Carto\CQL\Part;

class FromClause
{
	private
		$_associatedIdentiferDeclarations,
		$_entityIdentifierDeclarations;
		
	public function __construct(array $entityIdentifierDeclarations=array(),array $associatedIdentiferDeclarations=array())
	{
		$this->_entityIdentifierDeclarations = $entityIdentifierDeclarations;
		$this->_associatedIdentiferDeclarations = $associatedIdentiferDeclarations;
	}
	
	public function associatedIdentifierDeclarations()
	{
		return $this->_associatedIdentiferDeclarations;
	}
	
	public function entityIdentifierDeclarations()
	{
		return $this->_entityIdentifierDeclarations;
	}
	
	public function translate($translator)
	{
		return $translator->translateFromClause($this);
	}
}