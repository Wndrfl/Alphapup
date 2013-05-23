<?php
namespace Alphapup\Component\Carto;

/**
 * 	Saves/Maps information on how a query's select columns are mapped to the 
 *  various entities that it is querying for.
 **/
class ResultMapping
{
	public
	
		// aliases to class names
		$_entityToAliasMapping = array(),
		
		// maps child aliases to parent aliases
		$_childAliasToParentAliasMapping = array(),
		
		// maps child aliases to the property in the
		// parent that connects them
		$_childAliasToParentPropertyMapping = array(),
		
		// maps query column names to alias owners
		$_columnToOwnerMapping = array(),
		
		// stores columns that need are used for 
		// relations and associations
		$_metaColumnMapping = array(),
		
		// maps properties to columns
		$_propertyToColumnMapping = array(),
		
		// maps properties to their owners
		$_propertyToOwnerMapping = array();
	
	public function aliasMap()
	{
		return $this->_entityToAliasMapping;
	}
	
	public function childAliasToParentAliasMap()
	{
		return $this->_childAliasToParentAliasMapping;
	}
	
	public function childrenAliasesFor($entityAlias)
	{
		return array_keys($this->_childAliasToParentAliasMapping,$entityAlias);
	}
	
	public function childRelationPropertyMap()
	{
		return $this->_childAliasToParentPropertyMapping;
	}
	
	public function columnOwner($column)
	{
		return (isset($this->_columnToOwnerMapping[$column])) ? $this->_columnToOwnerMapping[$column] : false;
	}
	
	public function columnOwnerMap()
	{
		return $this->_columnToOwnerMapping;
	}
	
	public function entityForAlias($alias)
	{
		return $this->_entityToAliasMapping[$alias];
	}
			
	public function expand()
	{
		echo "<pre>";
		echo 'Alias map:'."<br />";
		print_r($this->_entityToAliasMapping);
		
		echo 'Column owner map:'."<br />";
		print_r($this->_columnToOwnerMapping);
		
		echo 'Property mappings:'."<br />";
		print_r($this->_propertyToColumnMapping);
		
		echo 'Property owner map:'."<br />";
		print_r($this->_propertyToOwnerMapping);
		
		echo 'Child alias to parent alias map:'."<br />";
		print_r($this->_childAliasToParentAliasMapping);
		
		echo 'Child relation property map:'."<br />";
		print_r($this->_childAliasToParentPropertyMapping);
	}
	
	public function mapEntity($entityName,$alias)
	{
		$this->_entityToAliasMapping[$alias] = $entityName;
	}
	
	public function mapJoin($parentAlias,$joinEntityName,$joinAlias,$relationProperty)
	{
		$this->_entityToAliasMapping[$joinAlias] = $joinEntityName;
		$this->_childAliasToParentAliasMapping[$joinAlias] = $parentAlias;
		$this->_childAliasToParentPropertyMapping[$joinAlias] = $relationProperty;
	}
	
	public function mapMeta($entityAlias,$columnName)
	{
		$this->_metaColumnMapping[$columnName] = $columnName;
		$this->_columnToOwnerMapping[$columnName] = $entityAlias;
	}
	
	public function mapProperty($entityAlias,$columnName,$propertyName)
	{
		$this->_propertyToColumnMapping[$columnName] = $propertyName;
		$this->_columnToOwnerMapping[$columnName] = $entityAlias;
		//$this->_propertyToOwnerMapping[$columnName] = $this->_entityToAliasMapping[$entityAlias];
	}
	
	public function metaColumn($column)
	{
		return (isset($this->_metaColumnMapping[$column])) ? $this->_metaColumnMapping[$column] : false;
	}
	
	public function parentForAlias($alias)
	{
		return (isset($this->_childAliasToParentAliasMapping[$alias])) ? $this->_childAliasToParentAliasMapping[$alias] : false;
	}
	
	public function propertiesForEntity($entityName)
	{
		die('fix resultmapping.php line 110');
		$propertyAliases = array_keys($this->_propertyToOwnerMapping,$entityName);
		
		$properties = array();
		foreach($propertyAliases as $propertyAlias) {
			$properties[] = $this->_propertyToColumnMapping[$propertyAlias];
		}
		
		return $properties;
	}
	
	public function propertyForColumn($column)
	{
		return (isset($this->_propertyToColumnMapping[$column])) ? $this->_propertyToColumnMapping[$column] : false;
	}
	
	public function propertyToColumnMappings()
	{
		return $this->_propertyToColumnMapping;
	}
	
	public function propertyToOwnerMappings()
	{
		return $this->_propertyToOwnerMapping;
	}
}