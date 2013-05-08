<?php
namespace Alphapup\Component\Helper;

use Alphapup\Component\Helper\BaseHelper;

class FilterHelper extends BaseHelper
{
	private
		$_filters = array();
		
	public function __construct($filters=array())
	{
		$this->setFilters($filters);
	}
	
	public function filter($name,$content)
	{
		if(!isset($this->_filters[$name])) {
			// ERROR
			return false;
		}
		
		$filter = $this->_filters[$name];
		
		return $filter->filter($content);
	}
	
	public function name()
	{
		return 'filter';
	}
	
	public function setFilters($filters=array())
	{
		foreach($filters as $filter) {
			$this->_filters[$filter->name()] = $filter;
		}
	}
}