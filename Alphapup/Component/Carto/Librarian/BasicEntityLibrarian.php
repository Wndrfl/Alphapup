<?php
namespace Alphapup\Component\Carto\Librarian;

use Alphapup\Component\Carto\Carto;
use Alphapup\Component\Carto\Hydrator;
use Alphapup\Component\Carto\Mapping;
use Alphapup\Component\Carto\Proxy\OneToManyProxy;
use Alphapup\Component\Carto\Proxy\ManyToManyProxy;
use Alphapup\Component\Carto\Proxy\Proxy;
use Alphapup\Component\Carto\ResultMapping;
use Alphapup\Component\Carto\SQL\SQLBuilder;

class BasicEntityLibrarian
{
	private
		$_carto,
		$_hydrator,
		$_mapping;
	
	private
		$_insertSql,
		$_queuedInserts=array();
		
	public function __construct(Carto $carto,Mapping $mapping)
	{
		$this->_carto = $carto;
		$this->_mapping = $mapping;
		$this->_hydrator = new Hydrator($this->_carto);
	}
	
	private function _addToLibrary($entity)
	{
		$this->_carto->library()->addManaged($entity);
	}
	
	private function _generateInsertColumnList()
	{
		$columns = array();
		foreach($this->_mapping->propertyNames() as $propertyName) {
			if(!$this->_mapping->idGenerationIsAuto() || !in_array($propertyName,$this->_mapping->ids())) {
				$columns[] = $this->_mapping->columnName($propertyName);
			}
		}
		
		foreach($this->_mapping->associations() as $propertyName => $assoc) {
			if($assoc['isOwningSide'] && $assoc['type'] & Mapping::TO_ONE) {
				$columns[] = $assoc['local'];
			}
		}
		
		return $columns;
	}
	
	private function _generateInsertSql()
	{
		if(!empty($this->_insertSql)) {
			return $this->_insertSql;
		}
		
		$sql = 'INSERT INTO '.$this->_mapping->tableName();
		
		$columns = $this->_generateInsertColumnList();
		$columns = array_unique($columns);
        $values = array_fill(0, count($columns), '?');

		$sql .= ' ('.implode(', ',$columns).')';
		$sql .= ' VALUES ';
		$sql .= '('.implode(', ',$values).')';
		
		$this->_insertSql = $sql;
		
		return $this->_insertSql;
	}
	
	private function _prepareInsertData($entity)
	{
		return $this->_prepareUpdateData($entity);
	}
	
	private function _prepareUpdateData($entity)
	{
		$data = array();
		$library = $this->_carto->library();
		
		foreach($library->entityChangeset($entity) as $propertyName => $change) {
			
			$oldValue = $change[0];
			$newValue = $change[1];
			if($assoc = $this->_mapping->propertyAssociation($propertyName)) {
				
				// Only owning side of x-1 associations can have a FK column.
				if(!$assoc['isOwningSide'] || !($assoc['type'] & Mapping::TO_ONE)) {
					continue;
				}
				
				if($newValue !== null) {
					
					$oid = $library->createId($newValue);
					if(isset($this->_queuedInserts[$oid]) || $library->isScheduledForInsert($newValue)) {
						// The associated entity $newVal is not yet persisted, so we must
						// set $newVal = null, in order to insert a null value and schedule an
						// extra update on the Library
						$library->scheduleForExtraUpdate($entity,array(
							$propertyName => array(null,$newValue)
						));
						$newValue = null;
					}
				}
				
				if($newValue !== null) {
					$newValId = $library->entityIdentifier($newValue);
				}
				
				$targetMapping = $this->_carto->mapping($assoc['entity']);
				
				if($newValue === null) {
					$data[$this->_mapping->tableName()][$assoc['local']] = null;
				
				}else{
					$data[$this->_mapping->tableName()][$assoc['local']] = $newValId[$targetMapping->propertyName($assoc['foreign'])];
				}
				
			}else{
				$data[$this->_mapping->tableName()][$this->_mapping->columnName($propertyName)] = $newValue;
			}
		}
		
		return $data;
	}
	
	public function addInsert($entity)
	{
		$this->_queuedInserts[spl_object_hash($entity)] = $entity;
	}
	
	public function dexter()
	{
		return $this->_carto->dexter();
	}
	
	public function executeDelete($entity)
	{
		$sql = 'DELETE FROM '.$this->_mapping->tableName().' WHERE ';
		
		$ids = $this->_mapping->ids();
		$parts = array();
		$params = array();
		foreach($ids as $propertyName) {
			$parts[] = $this->_mapping->columnName($propertyName).' = ?';
			$params[] = $this->_mapping->entityValue($entity,$propertyName);
		}
		
		$sql .= implode(' && ',$parts);
		
		return $this->_carto->dexter()->execute($sql,$params)->rowCount();
	}
	
	public function executeInserts()
	{ 
		if(!$this->_queuedInserts) {
			return;
		}
		
		$stmt = $this->_carto->dexter()->statement($this->_generateInsertSql());
		
		$entityProperties = $this->_mapping->propertyNames();
		
		$insertIds = array();
		
		foreach($this->_queuedInserts as $entity) {
			
			$data = $this->_prepareInsertData($entity);
			
			$query = $this->_carto->dexter()->query($stmt->sql());
			
			if(isset($data[$this->_mapping->tableName()])) {
				$query->setParams($data[$this->_mapping->tableName()]);
			}	
			
			$stmt->execute($query);
			
			$id = $this->_carto->dexter()->lastInsertId();
			$insertIds[$id] = $entity;
		}
		
		$stmt->closeCursor();
		$this->_queuedInserts = array();
		
		return $insertIds;
	}
	
	public function executeUpdate($entity)
	{
		if(get_class($entity) != $this->_mapping->className()) {	
			return false;
		}
		
		$parts = array();
		$params = array();
		foreach($this->_mapping->propertyNames() as $propertyName) {
			$parts[] = $this->_mapping->columnName($propertyName).' = ?';
			$params[] = $this->_mapping->entityValue($entity,$propertyName);
		}
		$updates = implode(', ',$parts);
		
		$ids = $this->_mapping->entityIdValues($entity);
		$idParts = array();
		foreach($ids as $propertyName => $id) {
			$idParts[] = $this->_mapping->columnName($propertyName).' = ?';
			$params[] = $id;
		}
		$id = implode(' && ',$idParts);
		
		$sql = 'UPDATE '.$this->_mapping->tableName().' SET '.$updates.' WHERE '.$id;
		
		$rows = $this->_carto->dexter()->execute($sql,$params)->rowCount();
		
		return $rows;
	}
	
	public function fetch($id)
	{
		$qb = new SQLBuilder();
		
		$rm = new ResultMapping();
		$rm->mapEntity($this->_mapping->entityName(),$this->_mapping->entityName());
		
		$columns = array();
		
		// Get regular columns
		foreach($this->_mapping->columnNames() as $columnName) {
			$columns[] = $qb->expr()->column($this->_mapping->tableName(),$columnName);
			$rm->mapProperty($this->_mapping->entityName(),$columnName,$this->_mapping->propertyName($columnName));
		}
		
		// Get mapping columns
		foreach($this->_mapping->associations() as $assoc) {
			if(isset($assoc['local'])) {
				$columns[] = $qb->expr()->column($this->_mapping->tableName(),$assoc['local']);
				$rm->mapMeta($this->_mapping->entityName(),$assoc['local']);
			}
			
		}
		
		$qb->select($columns);
		
		$qb->from($this->_mapping->tableName());
				
		$ids = $this->_mapping->ids();
		$qb->where(
			$qb->expr()->isEqualTo(
				$qb->expr()->column($this->_mapping->tableName(),$this->_mapping->columnName($ids[0])),
				'?'
			)
		);
		
		$qb->limit(1);
		
		$sql = $qb->sql();
		
		$rows = $this->_carto->dexter()->execute($sql,array($id))->results();
		
		if(!isset($rows[0])) {
			return false;
		}
		
		$results = $this->_hydrator->hydrateAll($rows,$rm);
		
		return $results[0];
	}
	
	public function fetchBy(array $criteria=array(),$orderBy=null,$limit=null,$offset=null,array $options=array())
	{
		$qb = new SQLBuilder();
		
		$rm = new ResultMapping();
		$rm->mapEntity($this->_mapping->entityName(),$this->_mapping->entityName());
		
		$columns = array();
		
		// Get regular columns
		foreach($this->_mapping->columnNames() as $columnName) {
			$columns[] = $qb->expr()->column($this->_mapping->tableName(),$columnName);
			$rm->mapProperty($this->_mapping->entityName(),$columnName,$this->_mapping->propertyName($columnName));
		}
		
		// Get mapping columns
		foreach($this->_mapping->associations() as $assoc) {
			if(isset($assoc['local'])) {
				$columns[] = $qb->expr()->column($this->_mapping->tableName(),$assoc['local']);
				$rm->mapMeta($this->_mapping->entityName(),$assoc['local']);
			}
			
		}
		
		$qb->select($columns);
		
		$qb->from($this->_mapping->tableName());
				
		$conditions = array();
		$params = array();
		foreach($criteria as $propertyName => $value) {
			if($columnName = $this->_mapping->columnName($propertyName)) {
				$conditions[] = $qb->expr()->isEqualTo(
					$qb->expr()->column($this->_mapping->tableName(),$this->_mapping->columnName($propertyName)),
					'?'
				);
				$params[] = $value;
			}elseif($assoc = $this->_mapping->propertyAssociation($propertyName)) {
				$conditions[] = $qb->expr()->isEqualTo(
					$qb->expr()->column($this->_mapping->tableName(),$assoc['local']),
					'?'
				);
				$params[] = $value;
			}
		}
		$qb->where($conditions);
		
		if(is_array($orderBy)) {
			foreach($orderBy as $col => $dir) {
				$qb->orderBy($col,$dir);
			}
		}
		
		if(!is_null($limit) || !is_null($offset)) {
			$qb->limit($limit,$offset);
		}
		
		$sql = $qb->sql();
		
		$rows = $this->_carto->dexter()->execute($sql,$params)->results();
		
		if(!isset($rows[0])) {
			return false;
		}
		
		$results = $this->_hydrator->hydrateAll($rows,$rm,$options);
		
		return $results;
	}
	
	public function fetchByQuery($sql,$params=array(),ResultMapping $rm)
	{
		$rows = $this->_carto->dexter()->execute($sql,$params)->results();
		$results = $this->_hydrator->hydrateAll($rows,$rm);
		
		return $results;
	}
	
	public function fetchOne(array $criteria=array(),array $options=array())
	{
		$results = $this->fetchBy($criteria,null,1,null,$options);
		
		if(isset($results[0])) {
			return $results[0];
		}
		
		return false;
	}
	
	public function getOrCreateEntity(array $data=array(),array $options=array())
	{
		$className = $this->_mapping->className();
		
		$ids = $this->_mapping->ids();
		
		if($this->_mapping->idIsCompound()) {
			$identifier = array();
			foreach($ids as $id) {
				if($assoc = $this->_mapping->propertyAssociation($id)) {
					$identifier[$id] = $data[$assoc['local']];
				}else{
					$identifier[$id] = $data[$id];
				}
			}
		}else{
			if($assoc = $this->_mapping->propertyAssociation($ids[0])) {
				$identifier[$ids[0]] = $data[$assoc['local']];
			}else{
				$identifier[$ids[0]] = $data[$ids[0]];
			}
		}
		
		$library = $this->_carto->library();
		
		// try to find an existing copy first
		if($entity = $library->tryGetById($identifier,$className)) {
			$oid = $library->createId($entity);
			if($entity instanceof Proxy && !$entity->__isInitialized__) {
				$entity->__isInitialized = true;
				$overrideLocal = true;
			}else{
				$overrideLocal = (isset($options['useEntity']));
				
				// if a specific entity is given to simply refresh,
				// check that its the same one
				if($overrideLocal && $options['useEntity'] !== $entity) {
					$overrideLocal = false;
				}
			}
		}else{
		
			// create a fresh entity
			if(isset($options['useEntity'])) {
				$entity = $options['useEntity'];
			}else{
				$entity = new $className;
			}
			
			$overrideLocal = true;
		}
		
		// override local values if need be
		if($overrideLocal) {
			// set values according to mapping annotations
			foreach($this->_mapping->propertyNames() as $propertyName) {
				if(isset($data[$propertyName])) {
					$this->_mapping->setEntityValue($entity,$propertyName,$data[$propertyName]);
				}
			}
		
			// set proxies for associations
			foreach($this->_mapping->associations() as $association) {
			
				if(isset($options['eager'])) {
					if(is_array($options['eager']) && in_array($association['property'],$options['eager'])) {
						$association['lazy'] = false;
					}elseif($options['eager'] == $association['property']) {
						$association['lazy'] = false;
					}
				}

				if(isset($options['lazy'])) {
					if(is_array($options['lazy']) && in_array($association['property'],$options['lazy'])) {
						$association['lazy'] = true;
					}elseif($options['lazy'] == $association['property']) {
						$association['lazy'] = true;
					}
				}
			
				// Figure out TO_ONE relations
				if($association['type'] & Mapping::TO_ONE) {
				
					// if owner, it can be lazy
					if($association['isOwningSide']) {
						$joinColumnValue = (isset($data[$association['local']])) ? $data[$association['local']] : null;
				
						$assocMapping = $this->_carto->mapping($association['entity']);

						$associatedId = array();
						$associatedId[$assocMapping->propertyName($association['foreign'])] = $joinColumnValue;

						// if lazy loading, use proxy
						if($association['lazy'] == true) {
							$assocEntity = $this->_carto->proxyFactory()->proxy($association['entity'],$associatedId);

						// eager load
						}else{
							$assocEntity = $this->loadOneToOneEntity($entity,$association,$associatedId);
						}

						$this->_mapping->setEntityValue($entity,$association['propertyName'],$assocEntity);
				
					// if not owner, it can't be lazy
					}else{
						$this->_mapping->setEntityValue(
							$entity,
							$association['propertyName'],
							$this->loadOneToOneEntity($entity,$association)
						);
					}
				
				}elseif($association['type'] == Mapping::ONE_TO_MANY) {
				
					$targetMapping = $this->_carto->mapping($association['entity']);
					$targetLibrarian = $this->_carto->library()->librarian($association['entity']);
					$targetAssoc = $targetMapping->propertyAssociation($association['mappedBy']);
				
					$identifier = array(
						$targetAssoc['propertyName'] => 
						$this->_mapping->entityValue($entity,$this->_mapping->propertyName($targetAssoc['foreign']))
					);
				
					$collectionProxy = new CollectionProxy(
						$this->_carto,
						$this->_mapping,
						new ArrayCollection()
					);
					
					$collectionProxy->setOwner($entity,$association);

					// if lazy loading, use proxy
					if($association['lazy'] === false) {
						$collectionProxy->__load();
					}

					$this->_mapping->setEntityValue($entity,$association['propertyName'],$collectionProxy);
				
				}elseif($association['type'] == Mapping::MANY_TO_MANY) {
					$targetMapping = $this->_carto->mapping($association['entity']);
					$targetLibrarian = $this->_carto->library()->librarian($association['entity']);
				
					if($association['isOwningSide']) {
						$ownerAssoc = $association;
						$localIdValue = $this->_mapping->entityValue(
																$entity,
																$this->_mapping->propertyName($ownerAssoc['local'])
																);
					}else{
						$ownerAssoc = $targetMapping->propertyAssociation($association['mappedBy']);
						$localIdValue = $this->_mapping->entityValue(
																$entity,
																$this->_mapping->propertyName($ownerAssoc['foreign'])
																);
					}

					if(!$ownerAssoc) {
						// DO EXCEPTION
						return false;
					}

					if($ownerAssoc['type'] != Mapping::MANY_TO_MANY) {
						// DO EXCEPTION
						return false;
					}

					$collectionProxy = new CollectionProxy(						
						$this->_carto,
						$this->_mapping,
						new ArrayCollection()
					);

					$collectionProxy->setOwner($entity,$association);

					// if lazy loading, use proxy
					if($association['lazy'] === false) {
						$collectionProxy->__load();
					}

					$this->_mapping->setEntityValue($entity,$association['propertyName'],$collectionProxy);
				}
			}
		}
		
		$this->_addToLibrary($entity);
		return $entity;	
	}
	
	public function loadOneToOneEntity($entity,array $association,array $identifier=array())
	{
		$targetMapping = $this->_carto->mapping($association['entity']);
		
		if($association['isOwningSide']) {
			
			if($targetEntity = $this->_carto->library()->librarian($targetMapping->className())->fetchOne($identifier)) {
				$targetMapping->setEntityValue($targetEntity,$association['inversedBy'],$entity);
			}
			
		}else{
			
			$targetAssoc = $targetMapping->propertyAssociation($association['mappedBy']);
			
			$identifier[$targetAssoc['propertyName']] = $this->_mapping->entityValue(
																		$entity,
																		$this->_mapping->propertyName($targetAssoc['foreign'])
																		);
																		
			if($targetEntity = $this->_carto->library()->librarian($targetMapping->className())->fetchOne($identifier)) {
				$targetMapping->setEntityValue($targetEntity,$association['mappedBy'],$entity);
			}
		}
		
		return $targetEntity;
	}
	
	public function loadOneToManyCollection(array $assoc,$ownerEntity,CollectionProxy $collection)
	{
		$targetMapping = $this->_carto->mapping($assoc['entity']);
		$targetLibrarian = $this->_carto->library()->librarian($assoc['entity']);
		$targetAssoc = $targetMapping->propertyAssociation($assoc['mappedBy']);
	
		$identifier = array(
			$targetAssoc['propertyName'] => 
			$this->_mapping->entityValue($ownerEntity,$this->_mapping->propertyName($targetAssoc['foreign']))
		);
		
		$entities = $this->_librarian->fetchBy($identifier);
		foreach($entities as $entity) {
			$collection->add($entity);
		}
		
		return $collection;
	}
	
	public function loadManyToManyCollection(array $assoc,$ownerEntity,CollectionProxy $collection)
	{
		$joinTable = $assoc['joinTable'];
		$localJoinColumn = $assoc['joinColumns']['local'];
		$foreignJoinColumn = $assoc['joinColumns']['foreign'];
		
		$foreignLibrarian = $this->_carto->library()->librarian($assoc['entity']);
		$foreignMapping = $this->_foreignLibrarian->mapping();
		
		if($assoc['isOwningSide']) {
			$ownerAssoc = $assoc;
			$localJoinId = $this->_mapping->entityValue(
									$entity,
									$this->_mapping->propertyName($ownerAssoc['local'])
									);
			
		}else{
			$ownerAssoc = $targetMapping->propertyAssociation($association['mappedBy']);
			$localJoinId = $this->_mapping->entityValue(
									$entity,
									$this->_mapping->propertyName($ownerAssoc['foreign'])
									);
		}
		
		$joinTable = $ownerAssoc['joinTable'];
		
		$localJoinColumn = $ownerAssoc['joinColumns']['local'];
		
		$foreignJoinColumn = $ownerAssoc['joinColumns']['foreign'];
		$foreignJoinPropertyName = $ownerAssoc['foreign'];

		$foreignLibrarian = $this->_carto->library()->librarian($ownerAssoc['entity']);
		$foreignMapping = $this->_foreignLibrarian->mapping();
		
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
		
		$qb->leftJoin($joinTable,$joinTable,'ON',
			$qb->expr()->isEqualTo(
				$qb->expr()->column(
					$joinTable,
					$foreignJoinColumn
				)
				,$qb->expr()->column(
					$foreignMapping->tableName(),
					$foreignMapping->columnName($foreignJoinPropertyName)
				)
			)
		);
			
		$qb->where(
			$qb->expr()->isEqualTo(
				$qb->expr()->column(
					$joinTable,
					$localJoinColumn
				)
				,'?'
			)
		);
		$params = array($localJoinId);
		
		$sql = $qb->sql();
		
		die('manytomany sql: '.$sql);
		
		$entities = $this->_foreignLibrarian->fetchByQuery($sql,$params,$rm);
		
		foreach($entities as $entity) {
			$collection->add($entity);
		}
		
		return $collection;
	}
	
	public function mapping()
	{
		return $this->_mapping;
	}
	
	public function persist($entity)
	{
		$this->_carto->library()->persist($entity);
	}
	
	public function resultMapping()
	{
		return new ResultMapping();
	}
	
	public function sqlBuilder()
	{
		return new SQLBuilder();
	}
}