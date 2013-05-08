<?php
namespace Alphapup\Component\Tongues\Filter;

use Alphapup\Component\Tongues\Filter\FilterMatch;
use Alphapup\Component\Tongues\TongueString;

class FilterManager
{

	private
		$_filterExp = "/<(\w+)((?:\s+\w+(?:\s*=\s*(?:(?:\"[^\"]*\")|(?:'[^']*')|[^>\s]+))?)*)\s*>(.*?)(<\/\\1>)/", // matches <filter>string</filter>
		//$_filterExp = "/(<([\w]+)[^>]*>)(.*?)(<\/\\2>)/", // matches <filter>string</filter>
		$_filters;
		
	public function __construct($filters=array())
	{
		$this->setFilters($filters);
	}
	
	public function applyFilters(TongueString $string)
	{
		$filters = $this->getFilterMatches($string);
		foreach($filters as $filter) {
			if($f = $this->filter($filter->openingTagContent())) {
				$f->filter($string,$filter);
			}
		}
		$string->render();
		return $string;
	}
	
	public function filter($name)
	{
		if(isset($this->_filters[$name])) {
			return $this->_filters[$name];
		}
		// DO EXCEPTION
		return false;
	}
	
	/**
	 *  RETURNS:
	 * $filters[][0] = full match
	 * $filters[][1] = opening tag text
	 * $filters[][2] = attributes
	 * $filters[][3] = contents
	 * $filters[][4] = closing tag
	**/
	public function getFilterMatches(TongueString $string)
	{
		preg_match_all($this->_filterExp,$string->text(),$matches,PREG_OFFSET_CAPTURE);
		$filterMatches = array();
		foreach($matches as $match) {
			foreach($match as $key => $val) {
				$filterMatches[$key][] = $val;
			}
		}
		
		$filters = array();
		foreach($filterMatches as $filterMatch) {
			$filter = new FilterMatch($filterMatch);
			$filters[] = $filter;
		}
		return $filters;
	}
	
	public function setFilter(FilterInterface $filter)
	{
		$this->_filters[$filter->name()] = $filter;
	}
	
	public function setFilters($filters=array())
	{
		foreach($filters as $filter) {
			$this->setFilter($filter);
		}
	}
}