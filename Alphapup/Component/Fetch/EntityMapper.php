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
	private
		$_annotationPrefix = 'Carto\\';
		
	private
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
	
	public function idColumns()
	{
		return $this->_ids;
	}
	
	public function idProperties()
	{
		return array_keys($this->_ids);
	}
	
	public function propertyNames()
	{
		return array_keys($this->_classProperties);
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