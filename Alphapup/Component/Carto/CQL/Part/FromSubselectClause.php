<?php
namespace Alphapup\Component\Carto\CQL\Part;

use Alphapup\Component\Carto\CQL\Part\FromClause;
use Alphapup\Component\Carto\CQL\Part\LimitClause;
use Alphapup\Component\Carto\CQL\Part\WhereClause;

class FromSubselectClause extends FromClause
{		
	private
		$_limitClause,
		$_whereClause;
		
	public function __construct(array $entityIdentifierDeclarations=array(),array $associatedIdentiferDeclarations=array())
	{
		parent::__construct($entityIdentifierDeclarations,$associatedIdentiferDeclarations);
	}
	
	public function limitClause()
	{
		return $this->_limitClause;
	}
	
	public function setLimitClause(LimitClause $limitClause)
	{
		$this->_limitClause = $limitClause;
		return $this;
	}
	
	public function setWhereClause(WhereClause $whereClause)
	{
		$this->_whereClause = $whereClause;
		return $this;
	}
	
	public function translate($translator)
	{
		return $translator->translateFromSubselectClause($this);
	}
	
	public function whereClause()
	{
		return $this->_whereClause;
	}
}