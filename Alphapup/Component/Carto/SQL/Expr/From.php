<?php
namespace Alphapup\Component\Carto\SQL\Expr;

class From
{
	private
		$_alias = '',
		$_from = '';
		
	public function __construct($from,$alias)
	{
		$this->_alias = $alias;
		$this->_from = $from;
	}
	
	public function alias()
	{
		return $this->_alias;
	}
	
	public function from()
	{
		return $this->_from;
	}
	
	public function __toString()
	{
		return $this->_from.' '.$this->_alias;
	}
}