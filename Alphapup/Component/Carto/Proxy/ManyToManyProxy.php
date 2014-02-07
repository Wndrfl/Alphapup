<?php
namespace Alphapup\Component\Carto\Proxy;

use Alphapup\Component\Carto\ArrayCollection;
use Alphapup\Component\Carto\Librarian\BasicEntityLibrarian;
use Alphapup\Component\Carto\Mapping;
use Alphapup\Component\Carto\Proxy\CollectionProxy;

class ManyToManyProxy extends CollectionProxy
{
	private
		$_foreignJoinColumn,
		$_foreignLibrarian,
		$_localJoinId,
		$_localJoinColumn,
		$_joinTable;
		
	public function __construct(Mapping $mapping,
								BasicEntityLibrarian $foreignLibrarian,
								$joinTable,
								$localJoinId,
								$localJoinColumn,
								$foreignJoinColumn)
	{
		parent::__construct();
		$this->_foreignJoinColumn = $foreignJoinColumn;
		$this->_foreignLibrarian = $foreignLibrarian;
		$this->_localJoinColumn = $localJoinColumn;
		$this->_localJoinId = $localJoinId;
		$this->_joinTable = $joinTable;
	}
	
	public function __load()
	{
		if(!$this->_isInitialized && $this->_foreignLibrarian) {
			$this->_isInitialized = true;

			$foreignMapping = $this->_foreignLibrarian->mapping();
				
			$foreignIds = $foreignMapping->ids();
			$foreignId = $foreignIds[0];
			
			$qb = $this->_foreignLibrarian->sqlBuilder();
			
			$rm = $this->_foreignLibrarian->resultMapping();
			$rm->mapEntity($foreignMapping->entityName(),$foreignMapping->entityName());
			
			// Get regular columns
			foreach($foreignMapping->columnNames() as $columnName) {
				$columns[] = $qb->expr()->column($foreignMapping->tableName(),$columnName);
				$rm->mapProperty($foreignMapping->entityName(),$columnName,$foreignMapping->propertyName($columnName));
			}

			// Get mapping columns
			foreach($foreignMapping->associations() as $assoc) {
				if(isset($assoc['local'])) {
					$columns[] = $qb->expr()->column($foreignMapping->tableName(),$assoc['local']);
					$rm->mapMeta($foreignMapping->entityName(),$assoc['local']);
				}
			}
			
			$qb->select($columns);
			
			$qb->from($foreignMapping->tableName(),$foreignMapping->tableName());
			
			$qb->leftJoin($this->_joinTable,$this->_joinTable,'ON',
				$qb->expr()->isEqualTo(
					$qb->expr()->column(
						$this->_joinTable,
						$this->_foreignJoinColumn
					)
					,$qb->expr()->column(
						$foreignMapping->tableName(),
						$foreignMapping->columnName($foreignId)
					)
				)
			);
				
			$qb->where(
				$qb->expr()->isEqualTo(
					$qb->expr()->column(
						$this->_joinTable,
						$this->_localJoinColumn
					)
					,'?'
				)
			);
			$params = array($this->_localJoinId);
			
			$sql = $qb->sql();
			
			$entities = $this->_foreignLibrarian->fetchByQuery($sql,$params,$rm);
			
			$this->setValues($entities);
			unset($this->_foreignLibrarian);
			
			$this->takeSnapshot();
		}
	}
}