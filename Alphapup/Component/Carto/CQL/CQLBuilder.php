<?php
namespace Alphapup\Component\Carto\CQL;

use Alphapup\Component\Carto\Carto;
use Alphapup\Component\Carto\Mapping;
use Alphapup\Component\Carto\CQL\Part\BaseCondition;
use Alphapup\Component\Carto\CQL\Part\Entity;
use Alphapup\Component\Carto\CQL\Part\IsEqualTo;
use Alphapup\Component\Carto\CQL\Part\IsGreaterThan;
use Alphapup\Component\Carto\CQL\Part\IsGreaterThanOrEqualTo;
use Alphapup\Component\Carto\CQL\Part\IsLessThan;
use Alphapup\Component\Carto\CQL\Part\IsLessThanOrEqualTo;
use Alphapup\Component\Carto\CQL\Part\IsNotEqualTo;
use Alphapup\Component\Carto\CQL\Part\Property;
use Alphapup\Component\Carto\SQL\SQLBuilder;

class CQLBuilder
{
	const
		Q_TYPE_FETCH = '1',
		Q_TYPE_UPDATE = '2',
		Q_TYPE_INSERT = '3';
		
	private
		$_carto,
		$_parts = array(
			'associated' => array(),
			'conditions' => array(),
			'entities' => array(),
			'limit' => false,
			'offset' => false,
			'properties' => array(),
			'root' => false,
		),
		$_rootLibrarian,
		$_rootMapping,
		$_type;
		
	public function __construct(Carto $carto)
	{
		$this->_carto = $carto;
	}
	
	private function _generateSelectQuery()
	{	
		$qb = $this->_carto->dexter()->SQLBuilder();

		$qb->setType(SQLBuilder::Q_TYPE_SELECT);
		
		$qb->from($this->_parts['root']->translate($qb));
		
		$mapping = $this->_parts['root']->mapping();
		
		// start gathering which columns we'll need specifically
		$properties = array();
		
		foreach($mapping->columnNames() as $columnName) {
			$alias = $mapping->entityName().$mapping->propertyName($columnName);
			$properties[] = $qb->createColumn($mapping->tableName(),$columnName,$alias);
		}
		
		foreach($this->_parts['associated'] as $association) {
			
			// make sure association is mapped
			if(!$assocMapping = $mapping->associationFor($association->name())) {
				// DO EXCEPTION
				return false;
			}
			
			// ONE TO ONE ASSOCIATION
			if($assocMapping['type'] == Mapping::ONE_TO_ONE) {
				
				// if one to one, the root entity will have a
				// local property that maps to a foreign property
				// (in the associated entity)
				$localProperty = $assocMapping['local'];
				$foreignProperty = $assocMapping['foreign'];
				
				$foreignMapping = $this->_carto->mapping($assocMapping['entity']);
				
				// create a join
				$qb->join($association->translate($qb),array(
					$qb->isEqualTo(
						$qb->createColumn($mapping->tableName(),$mapping->columnName($localProperty)),
						$qb->createColumn($foreignMapping->tableName(),$foreignMapping->columnName($foreignProperty))
					)
				));
				
				// make sure to select all columns for this entity
				foreach($foreignMapping->columnNames() as $columnName) {
					$properties[] = $qb->createColumn($association->translate($qb),$columnName);
				}
				
			}
			
		}
		
		foreach($properties as $property) {
			$qb->setColumn($property);
		}
		
		foreach($this->_parts['conditions'] as $condition) {
			$condition->translate($qb);
		}

		$query = $qb->query();
		
		echo $query->sql();
		
		return $query;
	}
	
	public function associated(array $associations=array())
	{
		foreach($associations as $association) {
			$associatedEntity = $this->createEntity($association);
			
			$this->setAssociated($associatedEntity);
		}
		return $this;
	}
	
	public function createEntity($entityName)
	{
		$mapping = $this->_carto->mapping($entityName);
		return new Entity($mapping);
	}
	
	public function createProperty($entityName,$propertyName,$alias=null)
	{
		$mapping = $this->_carto->mapping($entityName);
		return new Property($mapping,$propertyName,$alias);
	}
	
	public function entity($entityName)
	{
		return (isset($this->_parts['entities'][$entityName])) ? $this->_parts['entities'][$entityName] : false;
	}
	
	public function execute(array $options=array())
	{
		$query = $this->translate();
		return $this->_rootLibrarian->fetchByQuery($query);
	}
	
	public function fetch($entityName)
	{
		$this->setType('fetch');
		
		$entity = $this->createEntity($entityName);
		
		$this->_parts['root'] = $entity;
		
		$this->_rootLibrarian = $this->_carto->library()->librarian($entityName);
		$this->_rootMapping = $this->_carto->mapping($entityName);
		
		foreach($entity->propertyNames() as $propertyName) {
			$this->setProperty($this->createProperty($entity->name(),$propertyName));
		}
		
		return $this;
	}
	
	public function isEqualTo($compare1,$compare2,$bool=true)
	{
		$condition = new IsEqualTo($compare1,$compare2,$bool);
		return $condition;
	}
	
	public function isGreaterThan($compare1,$compare2,$bool=true)
	{
		$condition = new IsGreaterThan($compare1,$compare2,$bool);
		return $condition;
	}
	
	public function isGreaterThanOrEqualTo($compare1,$compare2,$bool=true)
	{
		$condition = new IsGreaterThanOrEqualTo($compare1,$compare2,$bool);
		return $condition;
	}
	
	public function isLessThan($compare1,$compare2,$bool=true)
	{
		$condition = new IsLessThan($compare1,$compare2,$bool);
		return $condition;
	}
	
	public function isLessThanOrEqualTo($compare1,$compare2,$bool=true)
	{
		$condition = new IsLessThanOrEqualTo($compare1,$compare2,$bool);
		return $condition;
	}
	
	public function isNotEqualTo($compare1,$compare2,$bool=true)
	{
		$condition = new IsNotEqualTo($compare1,$compare2,$bool);
		return $condition;
	}
	
	public function limit($limit,$offset=null)
	{
		$this->_parts['limit'] = intval($limit);
		if(!is_null($offset)) {
			$this->_parts['offset'] = intval($offset);
		}
		return $this;
	}
	
	public function query()
	{
		return $this->translate();
	}
	
	public function setAssociated(Entity $entity)
	{
		$this->_parts['associated'][$entity->name()] = $entity;
		return $this;
	}
	
	public function setCondition(BaseCondition $condition)
	{
		$this->_parts['conditions'][] = $condition;
		return $this;
	}
	
	public function setProperty(Property $property)
	{
		$this->_parts['properties'][$property->entityName()][$property->name()] = $property;
		return $this;
	}
	
	public function setType($type)
	{
		switch($type) {
			case 'fetch':
				$this->_type = self::Q_TYPE_FETCH;
				break;
				
			default:
				$this->_type = self::Q_TYPE_FETCH;
				break;
		}
		
		return $this;
	}
	
	public function translate()
	{
		switch($this->_type) {
			case self::Q_TYPE_FETCH:
				$query = $this->_generateSelectQuery();
				break;

			default:
				$query = $this->_generateSelectQuery();
				break;
		}

		return $query;
	}
	
	public function where($conditions = array())
	{
		if(is_array($conditions)) {
			foreach($conditions as $condition) {
				$this->setCondition($condition);
			}
		}else{
			$this->setCondition($conditions);
		}
		return $this;
	}
}