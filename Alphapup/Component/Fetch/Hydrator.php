<?php
namespace Alphapup\Component\Fetch;

use Alphapup\Component\Fetch\EntityMapper;
use Alphapup\Component\Fetch\Fetch;
use Alphapup\Component\Fetch\PublicLibrary;
use Alphapup\Component\Fetch\ResultMapper;

/**
 * The Hydrator is a tool that takes an empty
 * Entity and a set of data, and populates the Entity
 * with the respective data.
 */
class Hydrator
{
	private
		$_entities = array(),
		$_fetch,
		$_publicLibrary,
		$_resultMapper,
		$_rootEntityAliases = array(),
		$_rowValues = array(),
		$_tableAliasUIDs = array();
		
	public function __construct(Fetch $fetch, PublicLibrary $publicLibrary, ResultMapper $resultMapper)
	{
		$this->_fetch = $fetch;
		$this->_publicLibrary = $publicLibrary;
		$this->_resultMapper = $resultMapper;
	}
	
	private function _hydrateEntity($values=array(),$entityMapper)
	{
		// get the ids of this entity
		$ids = array();
		$idProperties = $entityMapper->idProperties();
		foreach($idProperties as $idProperty) {
			$ids[] = $values[$idProperty];
		}

		// either use an existing copy of this Entity,
		// or start from scratch
		$entity = $this->_publicLibrary->getOrCreateEntity($entityMapper,$ids);

		// loop thru properties and hydrate w/ values
		foreach($entityMapper->propertyNames() as $propertyName) {

			// if there is a value for this propertyName,
			// simply assign the value
			if(isset($values[$propertyName])) {
				$entityMapper->setPropertyValue($entity,$propertyName,$values[$propertyName]);

			// if this propertyName has an assocation,
			// figure it out
			}elseif($this->_entityMapper->associationForProperty($propertyName)) {
				die('yez');

			}
		}
		
		return $entity;
	}
	
	/**
	 * Organize a row's data into an array by
	 * entity alias. Array keys are property names
	 * and array values are property values
	 */
	public function formatRowData(array $row=array())
	{
		$rowData = array();
		
		foreach($row as $columnName => $value) {
			
			$className = $this->_resultMapper->classNameForColumn($columnName);
			$tableAlias = $this->_resultMapper->tableAliasForEntityName($className);

			if(!isset($rowData[$className])) {
				$rowData[$className] = array(
					'tableAlias' => $tableAlias,
					'values' => array()
				);
			}
			
			// NORMAL ENTITY PROPERTY
			if($propertyName = $this->_resultMapper->propertyNameForColumn($columnName)) {
				$rowData[$className]['values'][$propertyName] = $value;
				
			// META
			// This column is not mapped to a property in the entity
			// but instead is used to map an association
			}elseif($metaColumn = $this->_resultMapper->metaColumn($columnName)){
				$rowData[$className]['values'][$metaColumn] = $value;
			}
		}
		
		return $rowData;
	}
	
	public function hydrate(array $data=array())
	{	
		foreach($data as $row) {
			$this->hydrateRow($row);
		}
		
		$results = $this->organizeEntities();
		
		return $results;
		// DO CHILD ENTITY STUFF
		die('need to organize child entities into parent entities');
		
		return $this->_rootEntities;
	}
	
	public function hydrateRow(array $row=array())
	{
		$rowData = $this->formatRowData($row);
		
		foreach($rowData as $className => $data) {
			
			$tableAlias = $data['tableAlias'];
			$values = $data['values'];
			
			// translate className into an entityAlias
			$entityAlias = $this->_fetch->entityAlias($className);
			
			// get entityMapper for this entityName
			$entityMapper = $this->_fetch->entityMapper($entityAlias);
			
			// get entityName
			$entityName = $entityMapper->entityName();
			
			// get a UID for this entity
			$ids = array();
			$idProperties = $entityMapper->idProperties();
			foreach($idProperties as $idProperty) {
				$ids[] = $values[$idProperty];
			}
			$entityUID = implode('',$ids);
			
			// if this entity is a CHILD ENTITY
			if($parentTableAlias = $this->_resultMapper->parentAliasForTableAlias($tableAlias)) {
				
				// inject values into the entity
				$entity = $this->_hydrateEntity($values,$entityMapper);
				
				// add to entities
				$this->_entities[$entityAlias][$entityUID] = $entity;
				
				// get information about the parent
				$parentEntityName = $this->_resultMapper->entityNameForTableAlias($parentTableAlias);
				$parentEntityAlias = $this->_fetch->entityAlias($parentEntityName);
				$parentEntityMapper = $this->_fetch->entityMapper($parentEntityAlias);
				$parentAssociationInfo = $parentEntityMapper->findAssociationsInfoForClassName($entityMapper->entityName());
				$parentEntityUID = $this->_tableAliasUIDs[$parentTableAlias];
				
				// find all the different places the this entity is a child
				foreach($parentAssociationInfo as $assoc) {
					
					// grab parent entity from cache (above)
					$parentLocalId = $this->_rowValues[$parentTableAlias][$assoc['local']];
					
					// if it's not a match for this assoc, skip
					if($parentLocalId != $values[$entityMapper->propertyNameForColumn($assoc['foreign'])]) {
						continue;
					}
				
					// record entity as a child for its parent
					//$this->_childrenEntities[$parentEntityAlias][$parentLocalId][$entityAlias][$entityUID] = $entityUID;
					if(!isset($this->_childrenEntities[$parentEntityAlias][$parentEntityUID][$assoc['propertyName']])) {
						$this->_childrenEntities[$parentEntityAlias][$parentEntityUID][$assoc['propertyName']] = array(
							'type' => $assoc['type'],
							'values' => array()
						);
					}
					$this->_childrenEntities[$parentEntityAlias][$parentEntityUID][$assoc['propertyName']]['values'][] = array(
						'entityAlias' => $entityAlias,
						'entityUID' => $entityUID,
						'type' => $assoc['type']
					);
				}
			
			// if this entity is a ROOT ENTITY	
			}else{
				
				// inject values into the entity
				$entity = $this->_hydrateEntity($values,$entityMapper);

				// add to entities
				$this->_entities[$entityAlias][$entityUID] = $entity;
				
				// record entityAlias as a root
				$this->_rootEntityAliases[$entityAlias] = $entityName;
				
				// save a copy of the data
				$this->_rowValues[$tableAlias] = $values;
				
				// record the UID for this tableAlias
				$this->_tableAliasUIDs[$tableAlias] = $entityUID;
			}
			
		}
	}

	/**
	 * 	Actually create the tree of parents and children
	 *  AKA - assign children as property values of parents
	 **/
	public function organizeEntities()
	{
		$results = array();
		
		// Loop thru all the root entity aliases
		foreach($this->_rootEntityAliases as $rootEntityAlias => $rootEntityName) {
			
			$rootEntityMapper = $this->_fetch->entityMapper($rootEntityAlias);
			
			// Loop thru all rootEntities with this alias
			foreach($this->_entities[$rootEntityAlias] as $rootEntityUID => $rootEntity) {
				
				// Find all the children for this rootEntity
				foreach($this->_childrenEntities[$rootEntityAlias][$rootEntityUID] as $propertyName => $details) {

					// If it's to one, just map it directly
					if($details['type'] == EntityMapper::ONE_TO_ONE || $details['type'] == EntityMapper::ONE_TO_MANY) {
						
						$childEntity = $this->_entities[$details['values'][0]['entityAlias']][$details['values'][0]['entityUID']];
						$rootEntityMapper->setPropertyValue($rootEntity,$propertyName,$childEntity);
						
					}else{
						
						$collection = array();
						
						foreach($details['values'] as $value) {
							$childEntity = $this->_entities[$value['entityAlias']][$value['entityUID']];
							$collection[] = $childEntity;
						}
						
						$rootEntityMapper->setPropertyValue($rootEntity,$propertyName,$collection);
						
					}
					
				}
				
				$results[] = $rootEntity;
				
			}
			
		}
		
		return $results;
	}
}