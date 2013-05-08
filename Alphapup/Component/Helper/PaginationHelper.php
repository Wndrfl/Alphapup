<?php
namespace Alphapup\Component\Helper;

use Alphapup\Component\Helper\BaseHelper;

class PaginationHelper extends BaseHelper
{	
	private 
		$_grandTotal = 0,
		$_pageNum = 1,
		$_segmentSize = 25,
		$_maxCrumbs = 10;
	
	public function getCrumbs()
	{
		$endCrumb = $this->_grandTotal/$this->_segmentSize; // the highest crumb (page number) to display
		if($this->_grandTotal % $this->_segmentSize > 0) { $endCrumb++;} // if grand total divided by feed limit has remainder, +1 to accomodate

		/* Determine the starting number for the crumbs.
		 | If possible, we want the active crumb to be in the middle, 
		 | so, we check to see if the active $_pageNum is at least half of $_maxCrumbs.
		 | If it is, we can reduce the starting $paginationNum by 1/2 of the $_maxCrumbs.
		*/
		if($this->_pageNum > $this->_maxCrumbs/2) {
			$paginationNum = $this->_pageNum-($this->_maxCrumbs/2);
		}else{
			$paginationNum = 1;
		}

		/* Fill $pagination_crumbs with desired crumbs to display,
		 | starting with the $paginationNum determined above.
		 |
		 | -If $paginationNum becomes higher than the $endCrumb, stop.
		 | -If the number of crumbs in the array reaches the $_maxCrumbs, stop.
		*/
		$crumbs = array();
		for($i=1;$i<$this->_maxCrumbs && $paginationNum<$endCrumb;$i++) {
			$crumbs[] = $paginationNum;
			$paginationNum++;
		}
		
		return $crumbs;
	}
	
	public function name()
	{
		return 'pagination';
	}
	
	public function currPage()
	{
		return $this->_pageNum;
	}
	
	public function setMaxCrumbs($num)
	{
		if(preg_match("#[^0-9]#",$num)) {
			return;
		}
		$this->_maxCrumbs = $num;
	}
	
	public function setPage($page=0)
	{
		if(preg_match("#[^0-9]#",$page)) {
			return;
		}
		$this->_pageNum = ($page > 0) ? $page : 1;
	}
	
	public function setSegmentSize($size)
	{
		if(preg_match("#[^0-9]#",$size)) {
			return;
		}
		$this->_segmentSize = $size;
	}
	
	public function setTotal($total=0)
	{
		if(preg_match("#[^0-9]#",$total)) {
			return;
		}
		$this->_grandTotal = $total;
	}
	
	public function showNext()
	{
		// Should we show next page tab?
		$offsetNum = $this->_pageNum-1;
		$offset = $this->_segmentSize*$offsetNum;
		return ($this->_grandTotal > ($offset+$this->_segmentSize)) ? $this->_pageNum+1 : false;
	}
	
	public function showPrev()
	{
		// Should we show prev page tab?
		return ($this->_pageNum > 1) ? $this->_pageNum-1 : false;
	}
}