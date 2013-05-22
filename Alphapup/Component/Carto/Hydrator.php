<?php
namespace Alphapup\Component\Carto;

use Alphapup\Component\Carto\ArrayCollection;
use Alphapup\Component\Carto\Carto;

class Hydrator
{
	private
		$_carto,
		$_librarian,
		$_librarians = array(),
		$_mapping = array();
		
	// needs to be reset each hydration
	private
		$_childrenMap = array(),
		$_entityMap = array(),
		$_options = array(),
		$_resultMapping,
		$_resultSet = array(),
		$_rootAliases = array(),
		$_rootCount = 0;
		
	public function __construct(Carto $carto)
	{
		$this->_carto = $carto;
	}
	
	private function _getEntity(array $data,$entityAlias)
	{
		$librarian = $this->_librarians[$entityAlias];
		
		$entity = $librarian->getOrCreateEntity($data,$this->_options);
		
		return $entity;
	}
	
	private function _getRootAlias($resultMapping)
	{
		foreach($this->_resultMapping->aliasMap() as $alias => $entityName) {
			if($entityName == $this->_mapping->entityName()) {
				return $alias;
			}
		}
		
		// DO EXCEPTION
		return false;
	}
	
	public function _prepare()
	{
		$this->_childrenMap = 
		$this->_options = 
		$this->_rootAliases = 
		$this->_resultSet = 
		$this->_entityMap = array();
		
		$this->_rootCount = 0;
		
		// locally cache the mapping and librarians to reduce runs to _carto
		foreach($this->_resultMapping->aliasMap() as $alias => $entityName) {
			if(!isset($this->_mapping[$entityName])) {
				$mapping = $this->_carto->mapping($entityName);
				$this->_mapping[$entityName] = $mapping;
			}
			
			if(!isset($this->_librarians[$entityName])) {
				$librarian = $this->_carto->library()->librarian($entityName);
				$this->_librarians[$entityName] = $librarian;
			}
		}
	}
	
	/**
	 * Organize a row's data into an array by
	 * entity alias
	 */
	public function getRowData($data)
	{
		$rowData = array();
		
		foreach($data as $key => $value) {
			$alias = $this->_resultMapping->columnOwner($key);
			
			if(!isset($rowData[$alias])) {
				$rowData[$alias] = array();
			}
			
			// NORMAL ENTITY PROPERTY
			if($property = $this->_resultMapping->propertyForColumn($key)) {
				$rowData[$alias][$property] = $value;
				
			// META
			// This column is not mapped to a property in the entity
			// but instead is used to map an association
			}elseif($metaColumn = $this->_resultMapping->metaColumn($key)){
				$rowData[$alias][$metaColumn] = $value;
			}
		}
		
		return $rowData;
	}

	public function hydrateRow(array $data=array())
	{
		$rowData = $this->getRowData($data,$this->_resultMapping);
		$parents = array();
		
		foreach($rowData as $entityAlias => $data) {
			
			$entityName = $this->_resultMapping->entityForAlias($entityAlias);
			$entityMapping = $this->_mapping[$entityName];
			
			// get entity id(s)
			$ids = $entityMapping->ids();
			if($entityMapping->idIsCompound()) {
				$entityIds = array();
				foreach($ids as $id) {
					$entityIds[] = $data[$id];
				}
				$entityId = implode('',$entityIds);
			}else{
				$entityId = $data[$ids[0]];
			}
			
			// JOINED ENTITIES
			if($parentAlias = $this->_resultMapping->parentForAlias($entityAlias)) {
				$entity = $this->_getEntity($data,$entityName);
				$this->_entityMap[$entityAlias][$entityId] = $entity;
				
				// TODO: consolidate to figuring this out only once
				$parentEntityName = $this->_resultMapping->entityForAlias($parentAlias);
				$parentMapping = $this->_mapping[$parentEntityName];
				$parentAssociationInfo = $parentMapping->associationFor($entityMapping->entityName());

				$parentEntity = $this->_entityMap[$parentAlias][$parents[$parentAlias]];
				$parentLocalId = $parentMapping->entityValue($parentEntity,$parentAssociationInfo['local']);
			
				// map as child by
				// parentAlias -> parent's local value -> entity alias -> entity's id
				$this->_childrenMap[$parentAlias][$parentLocalId][$entityAlias][$entityId] = $entityId;
				
			// ROOT ENTITIES
			}else{
				
				if(isset($this->_entityMap[$entityName][$entityId])) {
					continue;
				}
				
				$this->_rootCount++;
				
				$entity = $this->_getEntity($data,$entityName);
				$this->_entityMap[$entityAlias][$entityId] = $entity;
				$this->_rootAliases[$entityAlias] = $entityAlias;
				$parents[$entityAlias] = $entityId;
			}
		}
	}
	
	public function hydrateAll($data,$resultMapping,array $options=array())
	{	
		$this->_resultMapping = $resultMapping;
		
		$this->_prepare();
		
		$this->_options = $options;
		
		foreach($data as $row) {
			$this->hydrateRow($row);
		}
		
		$results = array();
		$this->organizeEntities($results);
		
		return $results;
	}

	public function organizeEntities(&$results)
	{
		// Loop through root aliases and build tree
		// from top down
		foreach($this->_rootAliases as $rootAlias) {
			
			$rootEntityName = $this->_resultMapping->entityForAlias($rootAlias);
			
			$rootMapping = $this->_mapping[$rootEntityName];
			
			$childrenAliases = $this->_resultMapping->childrenAliasesFor($rootAlias);
			
			// figure out how to map each child to the parent
			$childrenMappingDetails = array();
			foreach($childrenAliases as $childAlias) {
				
				$childEntityName = $this->_resultMapping->entityForAlias($childAlias);
				$childMapping = $this->_mapping[$childEntityName];
				
				$rootAssociationInfo = $rootMapping->associationFor($childEntityName);
				
				$childrenMappingDetails[$childAlias] = $rootAssociationInfo;
			}
			
			// map children to each parent
			foreach($this->_entityMap[$rootAlias] as $rootEntity) {
				
				foreach($childrenMappingDetails as $childAlias => $details) {
					
					$localId = $rootMapping->entityValue($rootEntity,$details['local']);
					
					if(!isset($this->_childrenMap[$rootAlias][$localId][$childAlias])) {
						continue;
					}
					
					if($details['type'] == Mapping::ONE_TO_ONE) {
					
						foreach($this->_childrenMap[$rootAlias][$localId][$childAlias] as $childEntityId) {
							$childEntity = $this->_entityMap[$childAlias][$childEntityId];
							$rootMapping->setEntityValue($rootEntity,$details['property'],$childEntity);
						}
						
					}else{
						
						// is a collection based relationship
						$collection = new ArrayCollection();
						
						foreach($this->_childrenMap[$rootAlias][$localId][$childAlias] as $childEntityId) {
							$childEntity = $this->_entityMap[$childAlias][$childEntityId];
							$collection->setValue(null,$childEntity);
						}
						$rootMapping->setEntityValue($rootEntity,$details['property'],$collection);
						
					}
				}
				$results[] = $rootEntity;
			}
		}
	}
}