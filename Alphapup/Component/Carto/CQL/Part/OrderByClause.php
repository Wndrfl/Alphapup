<?php
namespace Alphapup\Component\Carto\CQL\Part;

use Alphapup\Component\Carto\CQL\Part\OrderExpression;

class OrderByClause
{
	private
		$_orders=array();
		
	public function __construct(array $orders=array())
	{
		$this->_orders = $orders;
	}
	
	public function orders()
	{
		return $this->_orders;
	}
	
	public function translate($translator)
	{
		return $translator->translateOrderByClause($this);
	}
}