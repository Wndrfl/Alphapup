<?php
namespace Alphapup\Component\Carto\CQL\Part;

use Alphapup\Component\Carto\CQL\Part\EntityExpression;

class FetchClause
{
	private
		$_fetchExpressions,
		$_isDistinct=false;
		
	public function __construct(array $fetchExpressions=array(),$isDistinct=false)
	{
		$this->_fetchExpressions = $fetchExpressions;
		$this->_isDistinct = false;
	}
	
	public function fetchExpressions()
	{
		return $this->_fetchExpressions;
	}
	
	public function isDistinct()
	{
		return $this->_isDistinct;
	}
	
	public function translate($translator)
	{
		return $translator->translateFetchClause($this);
	}
}