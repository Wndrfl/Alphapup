<?php
namespace Alphapup\Component\Carto;

use Alphapup\Component\Carto\Proxy\Proxy;
use Alphapup\Component\Introspect\Introspector\ClassIntrospector;

class Mapping
{
	const
		ONE_TO_ONE = 1,
		MANY_TO_ONE = 2,
		ONE_TO_MANY = 4,
		MANY_TO_MANY = 8;
		
	const
		TO_ONE = 3,
		TO_MANY = 8;
		
	private
		$_allowedAssociations,
		$_annotationPrefix = 'Carto\\',
		$_associationMappings = array(),
		$_columnNames = array(),
		$_identifiers = array(),
		$_introspector,
		$_isIdCompound = false,
		$_propertyNames = array(),
		$_propertyMappings = array();
		
	public function __construct(ClassIntrospector $introspector)
	{
		$this->_introspector = $introspector;
		$this->setup();
	}
	
	private function _validateAndCompleteAssociationMapping(array $annot,array $mapping)
	{	
		// mandatory
		if(!isset($mapping['propertyName'])) {
			// DO EXCEPTION
			return;
		}
		
		// mandatory
		if(!isset($annot['entity'])) {
			// DO EXCEPTION
			return;
		}
		$mapping['entity'] = ltrim($annot['entity'],'\\');
		
		$mapping['isOwningSide'] = true; // assume true until we hit mappedBy
		$mapping['mappedBy'] =  (isset($annot['mappedBy'])) ? $annot['mappedBy'] : null;
		$mapping['inversedBy'] = (isset($annot['inversedBy'])) ? $annot['inversedBy'] : null;
		
		if($mapping['mappedBy']) {
			$mapping['isOwningSide'] = false;
		}
		
		$mapping['lazy'] = (isset($annot['lazy'])) ? (bool)$annot['lazy'] : false;
		
		return $mapping;
	}
	
	private function _validateAndCompleteManyToManyMapping(array $annot,array $mapping)
	{
		$mapping = $this->_validateAndCompleteAssociationMapping($annot,$mapping);
		
		if($mapping['isOwningSide']) {
			$joinTable = lcfirst($this->entityName()).$mapping['entity'];
			$mapping['joinTable'] = (isset($annot['joinTable'])) ? $annot['joinTable'] : $joinTable;
			$mapping['local'] = (isset($annot['local'])) ? $annot['local'] : 'id';
			$mapping['foreign'] = (isset($annot['foreign'])) ? $annot['foreign'] : 'id';
			$mapping['joinColumns'] = array(
				'local' => (isset($annot['joinColumnLocal'])) ? $annot['joinColumnLocal'] : lcfirst($this->entityName()).'_id',
				'foreign' => (isset($annot['joinColumnForeign'])) ? $annot['joinColumnForeign'] : lcfirst($mapping['entity']).'_id'
			);
		}else{
			$mapping['joinTable'] = null;
			$mapping['local'] = null;
			$mapping['foreign'] = null;
			$mapping['joinColumns'] = array();
		}

		return $mapping;
	}

	private function _validateAndCompleteOneToManyMapping(array $annot,array $mapping)
	{
		$mapping = $this->_validateAndCompleteAssociationMapping($annot,$mapping);
		
		// OneToMany MUST not be owner
		if(!isset($annot['mappedBy'])) {
			// DO EXCEPTION
			return;
		}

		return $mapping;
	}
	
	private function _validateAndCompleteOneToOneMapping(array $annot,array $mapping)
	{
		$mapping = $this->_validateAndCompleteAssociationMapping($annot,$mapping);
		
		$mapping['local'] = (isset($annot['local'])) ? $annot['local'] : null;
		$mapping['foreign'] = (isset($annot['foreign'])) ? $annot['foreign'] : null;
		
		if(isset($annot['local'])) {
			$mapping['isOwningSide'] = true;
		}
		
		if($mapping['isOwningSide']) {
			$mapping['local'] = (isset($mapping['local'])) ? $mapping['local'] : lcfirst($mapping['entity']).'_id';
			$mapping['foreign'] = (isset($mapping['foreign'])) ? $mapping['foreign'] : 'id';
		}
		
		return $mapping;
	}
	
	/**
	* Maps stuff that every field will need mapped
	*/
	private function _validateAndCompletePropertyMapping($property,array $annot,array $mapping)
	{
		if(!isset($mapping['propertyName']) || strlen($mapping['propertyName']) == 0) {
			// DO EXCEPTION
			return;
		}
		
		$mapping['type'] = (isset($annot['type'])) ? $annot['type'] : 'string';
		
		if(!isset($annot['name'])) {
			$mapping['name'] = $mapping['propertyName'];
		}else{
			if($annot['name'][0] == '`') {
				$mapping['name'] = trim($annot['name'],'`');
				$mapping['quoted'] = true;
			}else{
				$mapping['name'] = $annot['name'];
			}
		}
		
		$this->_columnNames[$mapping['propertyName']] = $mapping['name'];
		$this->_propertyNames[$mapping['name']] = $mapping['propertyName'];
		
		if($idAnnot = $property->annotation($this->_annotationName('Id'))) {
			$mapping['id'] = true;
			
			if(!in_array($mapping['propertyName'],$this->_identifiers)) {
				$this->_identifiers[] = $mapping['propertyName'];
			}
			
			if(!$this->_isIdCompound && count($this->_identifiers) > 1) {
				$this->_isIdCompound = true;
			}
		}
		
		return $mapping;
	}
	
	private function _annotationName($name)
	{
		return $this->_annotationPrefix.$name;
	}
	
	public function allowedAssociations()
	{
		if(!empty($this->_allowedAssociations)) {
			return $this->_allowedAssociations;
		}
		
		foreach($this->associations() as $propertyName => $meta) {
			$this->_allowedAssociations[$meta['entity']] = $meta['entity'];
		}
		
		return $this->_allowedAssociations;
	}
	
	public function associationFor($className)
	{
		foreach($this->associations() as $association) {
			if($association['entity'] == $className) {
				return $association;
			}
		}
		return false;
	}
	
	public function associations()
	{
		return $this->_associationMappings;
	}
	
	public function className()
	{
		return $this->_introspector->name();
	}
	
	public function classNamespace()
	{
		return $this->_introspector->classNamespace();
	}
	
	public function columnName($propertyName)
	{
		return (isset($this->_columnNames[$propertyName])) ? $this->_columnNames[$propertyName] : false;
	}
	
	public function columnNames()
	{
		return $this->_columnNames;
	}
	
	public function entityIdValues($entity)
	{
		$values = array();
		foreach($this->ids() as $propertyName) {
			$value = $this->entityValue($entity,$propertyName);
			if($value !== null && $value !== false) {
				$values[$propertyName] = $value;
			}
		}
		return $values;
	}
	
	public function entityName()
	{
		return $this->_introspector->shortName();
	}
	
	public function entityValue($entity,$propertyName)
	{
		return $this->_introspector->property($propertyName)->value($entity);
	}
	
	public function hasMethod($method)
	{
		return $this->_introspector->hasMethod($method);
	}
	
	public function idGenerationIsAuto()
	{
		if($this->idIsCompound()) {
			return false;
		}
		
		$ids = $this->ids();
		$property = $this->_introspector->property($ids[0]);
		$annotation = $property->annotation('Id');
		$type = (isset($annotation['generation'])) ? $annotation['generation'] : 'auto';
		
		if(strtolower($type) == 'auto') {
			return true;
		}
		
		return false;
	}
	
	public function idIsCompound()
	{
		return $this->_isIdCompound;
	}
	
	public function ids()
	{
		return $this->_identifiers;
	}
	
	public function isReadOnly()
	{
		return false;
	}
	
	public function methods()
	{
		return $this->_introspector->methods();
	}
	
	public function propertyAssociation($propertyName)
	{
		$associations = $this->associations();
		
		if(isset($associations[$propertyName])) {
			return $associations[$propertyName];
		}
		return false;
	}
	
	public function propertyIsId($propertyName)
	{
		return in_array($propertyName,$this->ids());
	}
	
	public function propertyMappings()
	{
		return $this->_propertyMappings;
	}
	
	public function propertyName($columnName)
	{
		return $this->_propertyNames[$columnName];
	}
	
	public function propertyNames()
	{
		return $this->_propertyNames;
	}
	
	public function setEntityValue($entity,$propertyName,$value)
	{
		$property = $this->_introspector->property($propertyName);
		$property->setAccessible(true);
		$property->setValue($entity,$value);
	}
	
	public function setup()
	{
		foreach($this->_introspector->properties() as $property) {
			
			$mapping = array();
			$mapping['propertyName'] = $property->name();
			
			if($annot = $property->annotation($this->_annotationName('Column'))) {
				$mapping = $this->_validateAndCompletePropertyMapping($property,$annot,$mapping);
				$this->_propertyMappings[$mapping['propertyName']] = $mapping;
				
			}elseif($annot = $property->annotation($this->_annotationName('OneToOne'))) {
				$mapping['type'] = self::ONE_TO_ONE;
				$mapping = $this->_validateAndCompleteOneToOneMapping($annot,$mapping);
				$this->_associationMappings[$mapping['propertyName']] = $mapping;
				
			}elseif($annot = $property->annotation($this->_annotationName('OneToMany'))) {
				$mapping['type'] = self::ONE_TO_MANY;
				$mapping = $this->_validateAndCompleteOneToManyMapping($annot,$mapping);
				$this->_associationMappings[$mapping['propertyName']] = $mapping;
				
			}elseif($annot = $property->annotation($this->_annotationName('ManyToOne'))) {
				$mapping['type'] = self::MANY_TO_ONE;
				
				// A many-to-one mapping is essentially a one-one backreference
				$mapping = $this->_validateAndCompleteOneToOneMapping($annot,$mapping);
				$this->_associationMappings[$mapping['propertyName']] = $mapping;
				
			}elseif($annot = $property->annotation($this->_annotationName('ManyToMany'))) {
				$mapping['type'] = self::MANY_TO_MANY;
				$mapping = $this->_validateAndCompleteManyToManyMapping($annot,$mapping);
				$this->_associationMappings[$mapping['propertyName']] = $mapping;
				
			}
		}
	}
	
	public function tableName()
	{
		$table = $this->_introspector->annotation($this->_annotationName('Table'));
		if($table && isset($table['name'])) {
			$table = $table['name'];
		}else{
			$table = $this->entityName();
		}
		return $table;
	}
}