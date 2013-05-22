<?php
namespace Alphapup\Component\Carto\Librarian;

use Alphapup\Component\Carto\Carto;
use Alphapup\Component\Carto\Proxy\CollectionProxy;
use Alphapup\Component\Carto\Librarian\BaseCollectionLibrarian;

class ManyToManyLibrarian extends BaseCollectionLibrarian
{
	private function _generateInsertSql(array $assoc)
	{
		$sql = 'INSERT INTO '.$assoc['joinTable'].' ('.implode(', ',$assoc['joinColumns']).') '.
				'VALUES ('.implode(', ',array_fill(0,count($assoc['joinColumns']),'?')).')';
				
		return $sql;
	}
	
	private function _joinTableColumnParameters(CollectionProxy $collection,$element)
	{
		$params = array();
		
		$association = $collection->association();
		
		$mapping1 = $collection->mapping();
		$mapping2 = $this->_carto->mapping(get_class($element));
		
		// local first
		$params[] = $mapping1->entityValue($collection->owner(),$mapping1->propertyName($association['local']));
		$params[] = $mapping2->entityValue($element,$mapping2->propertyName($association['foreign']));
		
		return $params;
	}
}