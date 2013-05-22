<?php
namespace Alphapup\Component\Carto\Librarian;

use Alphapup\Component\Carto\Carto;
use Alphapup\Component\Carto\Proxy\CollectionProxy;

abstract class BaseCollectionLibrarian
{
	private
		$_carto,
		$_library;
		
	public function __construct(Carto $carto)
	{
		$this->_carto = $carto;
		$this->_library = $this->_carto->library();
	}
	
	public function executeCollectionInserts(CollectionProxy $collection)
	{
		echo "collection inserts";
		$insertDiff = $collection->insertDiff();
		
		$assoc = $collection->association();
		if(!$assoc['isOwningSide']) {
			return;
		}
		
		$sql = $this->_generateInsertSql($assoc);
		
		foreach($insertDiff as $element) {
			// $element is the element of the collection that changed,
			// which, since we are only worried about the owningSide,
			// would mean that $element is one of the inversed side
			die($sql);
			//$this->_carto->dexter()->execute($sql,$this->_joinTableColumnParameters($collection,$element));
		}
	}
}