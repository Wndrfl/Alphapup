<?php
namespace Alphapup\Component\Carto\SQL\Expr;

class Comparison
{
	const
		O_EQUALS		= '=',
		O_IN		= 'IN';
		
	private
		$_leftExpr = '',
		$_operator = '',
		$_rightExpr = '';
		
	public function __construct($leftExpr,$operator,$rightExpr)
	{
		$this->_leftExpr = $leftExpr;
		$this->_operator = $operator;
		$this->_rightExpr = $rightExpr;
	}
	
	public function __toString()
	{
		return $this->_leftExpr.' '.$this->_operator.' '.$this->_rightExpr;
	}
}