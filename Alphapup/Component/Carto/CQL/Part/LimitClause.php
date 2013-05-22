<?php
namespace Alphapup\Component\Carto\CQL\Part;

use Alphapup\Component\Carto\CQL\Part\LiteralExpression;

class LimitClause
{
	private
		$_limit,
		$_offset;
		
	public function __construct(LiteralExpression $limit,LiteralExpression $offset = null)
	{
		$this->_limit = $limit;
		$this->_offset = $offset;
	}
	
	public function limit()
	{
		return $this->_limit;
	}
	
	public function offset()
	{
		return $this->_offset;
	}
	
	public function translate($translator)
	{
		return $translator->translateLimitClause($this);
	}
}