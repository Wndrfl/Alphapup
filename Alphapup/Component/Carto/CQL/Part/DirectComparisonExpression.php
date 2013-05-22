<?php
namespace Alphapup\Component\Carto\CQL\Part;

use Alphapup\Component\Carto\CQL\Part\ConditionExpression;
use Alphapup\Component\Carto\CQL\Part\OperatorExpression;

class DirectComparisonExpression extends ConditionExpression
{
	private
		$_compare1,
		$_compare2,
		$_operator;
		
	public function __construct($compare1,OperatorExpression $operator,$compare2,$bool=true)
	{
		parent::__construct($bool);
		$this->_compare1 = $compare1;
		$this->_operator = $operator;
		$this->_compare2 = $compare2;
	}
	
	public function compare1()
	{
		return $this->_compare1;
	}
	
	public function compare2()
	{
		return $this->_compare2;
	}
	
	public function operator()
	{
		return $this->_operator;
	}
	
	public function translate($translator)
	{
		return $translator->translateDirectComparisonExpression($this);
	}
}