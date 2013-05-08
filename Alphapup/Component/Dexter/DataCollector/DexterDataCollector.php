<?php
namespace Alphapup\Component\Dexter\DataCollector;

use Alphapup\Component\Debug\DataCollector\BaseDataCollector;
use Alphapup\Component\Dexter\Dexter;

class DexterDataCollector extends BaseDataCollector
{
	private
		$_dexter,
		$_queries=array();
		
	public function __construct(Dexter $dexter)
	{
		$this->_dexter = $dexter;
	}
	
	public function __sleep()
	{
		return array('_queries');
	}
	
	public function collect()
	{
		foreach($this->_dexter->queries() as $query) {
			$q = array();
			$q['rowCount'] = $query->rowCount();
			$q['sql'] = $query->sql();
			$q['totalTime'] = $query->totalTime();
			$this->_queries[] = $q;
		}
		unset($this->_dexter);
	}
	
	public function name()
	{
		return 'dexter';
	}
	
	public function queries()
	{
		return $this->_queries;
	}
}