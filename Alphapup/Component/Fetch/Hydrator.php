<?php
namespace Alphapup\Component\Fetch;

use Alphapup\Component\Fetch\PublicLibrary;
use Alphapup\Component\Fetch\EntityMapper;

/**
 * The Hydrator is a tool that takes an empty
 * Entity and a set of data, and populates the Entity
 * with the respective data.
 */
class Hydrator
{
	private
		$_entityMapper,
		$_publicLibrary,
		$_rootEntities = array();
		
	public function __construct(PublicLibrary $publicLibrary, EntityMapper $entityMapper)
	{
		$this->_publicLibrary = $publicLibrary;
		$this->_entityMapper = $entityMapper;
	}
	
	public function hydrate(array $data=array())
	{
		foreach($data as $row) {
			$this->hydrateRow($row);
		}
		
		return $this->_rootEntities;
	}
	
	/**
	 * Each row contains data for an Entity as an array.
	 * Find the corresponding properties for the array 
	 * items, and store in an Entity.
	 */
	public function hydrateRow(array $row=array())
	{
		// get the ids of this row
		$ids = array();
		$idProperties = $this->_entityMapper->idColumns();
		foreach($idProperties as $idProperty) {
			$ids[] = $row[$idProperty];
		}
		
		// either use an existing copy of this Entity,
		// or start from scratch
		$entity = $this->_publicLibrary->getOrCreateEntity($this->_entityMapper,$ids);
		
		// loop thru properties and hydrate w/ values
		foreach($this->_entityMapper->propertyNames() as $propertyName) {
			$columnName = $this->_entityMapper->columnNameForProperty($propertyName);
			
			if(isset($row[$columnName])) {
				$this->_entityMapper->setPropertyValue($entity,$propertyName,$row[$columnName]);
			}
		}
		
		// add to root entities
		$this->_rootEntities[implode('',$ids)] = $entity;
	}

}