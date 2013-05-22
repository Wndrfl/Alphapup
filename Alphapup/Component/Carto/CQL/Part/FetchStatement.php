<?php
namespace Alphapup\Component\Carto\CQL\Part;

use Alphapup\Component\Carto\CQL\Part\FetchClause;
use Alphapup\Component\Carto\CQL\Part\FromClause;
use Alphapup\Component\Carto\CQL\Part\LimitClause;
use Alphapup\Component\Carto\CQL\Part\OrderByClause;
use Alphapup\Component\Carto\CQL\Part\WhereClause;

class FetchStatement
{
	private
		$_associationsClause,
		$_fetchClause,
		$_fromClause,
		$_limitClause,
		$_orderByClause,
		$_whereClause;
		
	public function __construct(FetchClause $fetchClause,FromClause $fromClause)
	{
		$this->_fetchClause = $fetchClause;
		$this->_fromClause = $fromClause;
	}
	
	public function associationsClause()
	{
		return $this->_associationsClause;
	}
	
	public function fetchClause()
	{
		return $this->_fetchClause;
	}
	
	public function fromClause()
	{
		return $this->_fromClause;
	}
	
	public function limitClause()
	{
		return $this->_limitClause;
	}
	
	public function orderByClause()
	{
		return $this->_orderByClause;
	}
	
	public function setAssociationsClause(AssociationsClause $associationsClause)
	{
		$this->_associationsClause = $associationsClause;
	}
	
	public function setLimitClause(LimitClause $limitClause)
	{
		$this->_limitClause = $limitClause;
	}
	
	public function setOrderByClause(OrderByClause $orderByClause)
	{
		$this->_orderByClause = $orderByClause;
	}
	
	public function setWhereClause(WhereClause $whereClause)
	{
		$this->_whereClause = $whereClause;
		return $this;
	}
	
	public function translate($translator)
	{
		return $translator->translateFetchStatement($this);
	}
	
	public function whereClause()
	{
		return $this->_whereClause;
	}
}