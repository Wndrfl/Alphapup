<?php
namespace Alphapup\Component\Fetch;

use Alphapup\Component\Introspect\Introspector\ClassIntrospector;

/**
 * An EntityMapper is a tool that introspects an
 * Entity to understand how the Entity relates to the 
 * data store and other Entities.
 * 
 * This information is then used by an EntityLibrarian
 * (and other tools) in their duties.
 * 
 * It is passed a ClassIntrospector that already has the Class
 * of an Entity loaded. It then runs processes on that Class.
 */
class EntityMapper
{
	const
		ONE_TO_ONE = 1,
		MANY_TO_ONE = 2,
		ONE_TO_MANY = 4,
		MANY_TO_MANY = 8;
		
	private
		$_annotationPrefix = 'Carto\\';
		
	private
		$_associations = array(),
		$_classIntrospector,
		$_classProperties = array(),
		$ids = array();
		
	public function __construct(ClassIntrospector $classIntrospector)
	{
		$this->_classIntrospector = $classIntrospector;
		$this->setup();
	}
	
	private function _annotationName($name)
	{
		return $this->_annotationPrefix.$name;
	}
	
	private function _associationDetails($propertyInspector,array $annot=array())
	{
		$assocDetails = array();
		
		// mandatory
		if(!isset($annot['entity']))
			throw new \Exception();
			
		// set property name
		$assocDetails['propertyName'] = $propertyInspector->name();
		$assocDetails['entity'] = ltrim($annot['entity'],'\\');
		$assocDetails['mappedBy'] =  (isset($annot['mappedBy'])) ? $annot['mappedBy'] : null;
		$assocDetails['inversedBy'] = (isset($annot['inversedBy'])) ? $annot['inversedBy'] : null;
		$assocDetails['lazy'] = (isset($annot['lazy'])) ? (bool)$annot['lazy'] : false;
		$assocDetails['ttl'] = (isset($annot['ttl'])) ? intval($annot['ttl']) : 0;
		
		return $assocDetails;
	}
	
	private function _oneToOneAssociationDetails($propertyInspector,array $annot=array())
	{
		// grab common association details
		$assocDetails = $this->_associationDetails($propertyInspector,$annot);
		
		$assocDetails['type'] = self::ONE_TO_ONE;
		
		// set local / foreign params
		$assocDetails['local'] = (isset($annot['local'])) ? $annot['local'] : null;
		$assocDetails['foreign'] = (isset($annot['foreign'])) ? $annot['foreign'] : null;
		
		// determine if owning side
		$assocDetails['isOwningSide'] = false;
		if(isset($annot['local'])) {
			$assocDetails['isOwningSide'] = true;
		}
		
		
		if($assocDetails['isOwningSide']) {
			
			// if no local supplied, default to the
			// name of the other entity and append "_id"
			if(is_null($assocDetails['local'])) {
				$assocDetails['local'] = lcfirst($assocDetails['entity']).'_id';
			}
			
			// if no foreign supplied default to "id"
			if(is_null($assocDetails['foreign'])) {
				$assocDetails['foreign'] = 'id';
			}
		}
		
		return $assocDetails;
	}
	
	private function _propertyDetails($propertyInspector,$annot)
	{
		$propertyDetails = array();
		
		// set property name
		$propertyDetails['propertyName'] = $propertyInspector->name();
		
		// set column name from annotation
		if(!$annot['name'])
			throw new \Exception();
		$propertyDetails['columnName'] = $annot['name'];
		
		// if this property is an ID, set as id
		$propertyDetails['isId'] = false;
		if($propertyInspector->annotation($this->_annotationName('Id'))) {
			$propertyDetails['isId'] = true;
			
			// save to Id's
			$this->_ids[$propertyDetails['propertyName']] = $propertyDetails['columnName'];
		}
		
		return $propertyDetails;
	}
	
	public function associations()
	{
		return $this->_associations;
	}
	
	public function associationForProperty($propertyName)
	{
		foreach($this->_associations as $assoc) {
			if($assoc['propertyName'] == $propertyName)
				return $assoc;
		}
		return false;
	}
	
	public function classProperties()
	{
		return $this->_classProperties;
	}
	
	public function columnNameForProperty($propertyName)
	{
		return (isset($this->_classProperties[$propertyName]))
			? $this->_classProperties[$propertyName]['columnName'] : null;
	}
	
	public function columnNames()
	{
		$columnNames = array();
		foreach($this->_classProperties as $propertyDetails) {
			$columnNames[$propertyDetails['propertyName']] = $propertyDetails['columnName'];
		}
		return $columnNames;
	}
	
	public function entityName()
	{
		return $this->_classIntrospector->shortName();
	}
	
	public function entityFullName()
	{
		return $this->_classIntrospector->name();
	}
	
	public function findAssociationsInfoForClassName($className)
	{
		$assocs = array();
		foreach($this->associations() as $association) {
			if($association['entity'] == $className) {
				$assocs[] = $association;
			}
		}
		return $assocs;
	}
	
	public function hasMethod($method)
	{
		return $this->_classIntrospector->hasMethod($method);
	}
	
	public function idColumns()
	{
		return $this->_ids;
	}
	
	public function idProperties()
	{
		return array_keys($this->_ids);
	}
	
	public function methods()
	{
		return $this->_classIntrospector->methods();
	}
	
	public function propertyNameForColumn($columnName)
	{
		foreach($this->_classProperties as $property) {
			if($property['columnName'] == $columnName)
				return $property['propertyName'];
		}
		return null;
	}
	
	public function propertyNames()
	{
		return array_keys($this->_classProperties);
	}
	
	public function propertyValue($entity,$propertyName)
	{
		return $this->_classIntrospector->property($propertyName)->value($entity);
	}
	
	public function setPropertyValue($entity,$propertyName,$value)
	{
		$property = $this->_classIntrospector->property($propertyName);
		$property->setAccessible(true);
		$property->setValue($entity,$value);
	}
	
	public function setup()
	{
		// Gather all the properties
		foreach($this->_classIntrospector->properties() as $propertyInspector) {
			
			// If property is COLUMN
			if($annot = $propertyInspector->annotation($this->_annotationName('Column'))) {
				$propertyDetails = $this->_propertyDetails($propertyInspector,$annot);
				$this->_classProperties[$propertyDetails['propertyName']] = $propertyDetails;
				
			// If property is ONE_TO_ONE
			}elseif($annot = $propertyInspector->annotation($this->_annotationName('OneToOne'))) {
				$assocDetails = $this->_oneToOneAssociationDetails($propertyInspector,$annot);
				$this->_associations[$assocDetails['propertyName']] = $assocDetails;
				
			// If property is ONE_TO_MANY
			}elseif($annot = $propertyInspector->annotation($this->_annotationName('OneToMany'))) {
			
			// If property is MANY_TO_ONE	
			}elseif($annot = $propertyInspector->annotation($this->_annotationName('ManyToOne'))) {
			
			// If property is MANY_TO_MANY	
			}elseif($annot = $propertyInspector->annotation($this->_annotationName('ManyToMany'))) {
				
			}
		}
	}
	
	public function tableName()
	{
		$tableName = $this->_classIntrospector->annotation($this->_annotationName('Table'));
		if($tableName && isset($tableName['name'])) {
			$tableName = $tableName['name'];
		}else{
			$tableName = $this->entityName();
		}
		return $tableName;
	}
}