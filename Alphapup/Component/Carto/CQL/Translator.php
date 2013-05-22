<?php
namespace Alphapup\Component\Carto\CQL;

use Alphapup\Component\Carto\Carto;
use Alphapup\Component\Carto\CQL\CQLQuery;
use Alphapup\Component\Carto\CQL\ParseResult;
use Alphapup\Component\Carto\CQL\Strategy;
use Alphapup\Component\Carto\Mapping;

class Translator
{		
	private
		$_associationTypes = array(),
		$_columnAliasCounter = 0,
		$_fetchedEntities = array(),
		$_tableAliasCounter = 0,
		$_tableAliasMap = array(),
		$_useQualifierTable = false;
		
	private
		$_carto,
		$_components,
		$_cqlQuery,
		$_resultMapping,
		$_strategy;
		
	public function __construct(Carto $carto,CQLQuery $cqlQuery,ParseResult $parseResult,Strategy $strategy)
	{
		$this->_carto = $carto;
		$this->_components = $parseResult->components();
		$this->_parseResult = $parseResult;
		$this->_resultMapping = $cqlQuery->resultMapping();
		$this->_strategy = $strategy;
	}
	
	private function _generateColumnAlias()
	{
		return 'carto_'.++$this->_columnAliasCounter;
	}
	
	private function _tableAliasFor($tableName,$cqlAlias=null)
	{
		$tableName .= (!is_null($cqlAlias)) ? '@['.$cqlAlias.']' : '';
		
		if(!isset($this->_tableAliasMap[$tableName])) {
			$alias = strtolower($tableName[0]).'_'.++$this->_tableAliasCounter;
			$this->_tableAliasMap[$tableName] = $alias;
		}
		
		return $this->_tableAliasMap[$tableName];
	}
	
	public function quoteString($string)
	{
		return "'".$string."'";
	}
	
	public function translateAssociatedIdentifierDeclaration(Part\AssociatedIdentifierDeclaration $aid)
	{
		if($aid->isOptional()) {
			$type = 'LEFT'; 
		}else{
			$type = 'INNER';
		}
		$part = $type.' JOIN';
		
		$component = $this->_components[$aid->alias()];
		$mapping = $component['mapping'];
		
		$parentComponent = $this->_components[$aid->parentAlias()];
		$parentMapping = $parentComponent['mapping'];
		$parentTableAlias = $this->_tableAliasFor($parentMapping->tableName(),$aid->parentAlias());
		
		$tableAlias = $this->_tableAliasFor($mapping->tableName(),$aid->alias());
		
		// make sure association is mapped to this entity
		if(!$assocAnnot = $parentMapping->propertyAssociation($aid->propertyName())) {
			// DO EXCEPTION
			return false;
		}
		
		// ONE TO ONE ASSOCIATION
		if($assocAnnot['type'] == Mapping::ONE_TO_ONE || $assocAnnot['type'] == Mapping::ONE_TO_MANY) {

			// if one to one, the root entity will have a
			// local property that maps to a foreign property
			// (in the associated entity)
			$assocProperty = $assocAnnot['foreign'];
		
			$part .= ' '.$mapping->tableName().' '.$tableAlias;
			$part .= ' ON '.$parentTableAlias.'.'.$parentMapping->columnName($assocAnnot['local']).' = '.$tableAlias.'.'.$mapping->columnName($assocProperty);
		
		}elseif($assocAnnot['type'] == Mapping::MANY_TO_MANY) {

			// set up middle table
			$middleTableName = $assocAnnot['joinTable'];
			$middleTableAlias = $this->_tableAliasFor($middleTableName);

			$middleParentColumnName = $assocAnnot['joinColumn'];

			if(!$foreignAssocAnnot = $mapping->associationFor($parentMapping->entityName())) {
				// DO EXCEPTION
				return false;
			}

			if($foreignAssocAnnot['joinTable'] != $middleTableName) {
				// DO EXCEPTION
				return false;
			}

			$middleForeignColumnName = $foreignAssocAnnot['joinColumn'];
			$parentColumnName = $parentMapping->columnName($assocAnnot['local']);
			
			$part .= ' '.$middleTableName.' '.$middleTableAlias;
			$part .= ' ON '.$parentTableAlias.'.'.$parentColumnName.' = '.$middleTableAlias.'.'.$middleParentColumnName;
			
			$part .= ' '.$type.' JOIN '.$mapping->tableName().' '.$tableAlias;
			$part .= ' ON '.$tableAlias.'.'.$mapping->columnName($foreignAssocAnnot['local']).' = '.$middleTableAlias.'.'.$foreignAssocAnnot['joinColumn'];
		}
		
		$this->_resultMapping->mapJoin($parentTableAlias,$mapping->entityName(),$tableAlias,$assocAnnot['property']);
		
		return $part;
	}
	
	public function translateAssociatedIdentifierDeclarations(array $aids=array())
	{
		$parts = array();
		foreach($aids as $aid) {
			$parts[] = $aid->translate($this);
		}
		
		$sql = implode(' ',$parts);
		
		return $sql;
	}
	
	public function translateDirectComparisonExpression(Part\DirectComparisonExpression $comparison)
	{
		$compare1 = $comparison->compare1()->translate($this);
		$compare2 = $comparison->compare2()->translate($this);
		
		$operator = $comparison->operator();
		
		switch($operator->type()) {
			case Part\OperatorExpression::O_EQUAL_TO:
				return $compare1.' = '.$compare2;
				break;
				
			case Part\OperatorExpression::O_GREATER_THAN:
				return $compare1.' > '.$compare2;
				break;

			case Part\OperatorExpression::O_GREATER_THAN_OR_EQUAL_TO:
				return $compare1.' >= '.$compare2;
				break;
				
			case Part\OperatorExpression::O_LESS_THAN:
				return $compare1.' < '.$compare2;
				break;
				
			case Part\OperatorExpression::O_LESS_THAN_OR_EQUAL_TO:
				return $compare1.' <= '.$compare2;
				break;
				
			case Part\OperatorExpression::O_NOT_EQUAL_TO:
				return $compare1.' != '.$compare2;
				break;
		}
	}
	
	public function translateFetchClause(Part\FetchClause $clause)
	{
		$sql = 'SELECT';
		if($clause->isDistinct()) {
			$sql .= ' DISTINCT';
		}
		
		$expressions = array();
		foreach($clause->fetchExpressions() as $fetchExpression) {
			$expressions[] = ' '.$this->translateFetchExpression($fetchExpression);
		}
		$sql .= ' '.implode(', ',$expressions);
		
		return $sql;
	}
	
	public function translateFetchExpression(Part\FetchExpression $expr)
	{
		$component = $this->_components[$expr->name()];
		$mapping = $component['mapping'];
		if(!isset($this->_fetchedEntities[$expr->alias()])) {
			$this->_fetchedEntities[$expr->alias()] = $mapping;
		}
		
		$tableAlias = $this->_tableAliasFor($mapping->tableName(),$expr->alias());
		
		$sql = '';
		
		$beginning = true;
		foreach($mapping->columnNames() as $columnName) {
			if(!$beginning) {
				$sql .= ', ';
			}else{
				$beginning = false;
			}
			
			$columnAlias = $this->_generateColumnAlias();
			
			$sql .= $tableAlias.'.'.$columnName.' AS '.$columnAlias;
			
			$this->_resultMapping->mapProperty($tableAlias,$columnAlias,$mapping->propertyName($columnName));
		}
		
		return $sql;
	}

	public function translateFetchStatement(Part\FetchStatement $stmt)
	{	
		$sql = $this->translateFetchClause($stmt->fetchClause());
		$sql .= ' '.$stmt->fromClause()->translate($this);
		
		if($this->_strategy->type() == Strategy::SIMPLE) {
			if($whereClause = $stmt->whereClause()) {
				$sql .= ' '.$whereClause->translate($this);
			}
			
			if($limitClause = $stmt->limitClause()) {
				$sql .= ' '.$limitClause->translate($this);
			}
		}
		
		return $sql;
	}
	
	public function translateFromClause(Part\FromClause $clause)
	{
		$sql = 'FROM';
		
		$parts = array();
		foreach($clause->entityIdentifierDeclarations() as $eid) {
			$component = $this->_components[$eid->alias()];
			$mapping = $component['mapping'];
		
			$tableAlias = $this->_tableAliasFor($mapping->tableName(),$eid->alias());
		
			$this->_resultMapping->mapEntity($eid->name(),$tableAlias);
		
			$parts[] = $mapping->tableName().' '.$tableAlias;
		}
		$sql .= ' '.implode(', ',$parts);
		
		$sql .=	' '.$this->translateAssociatedIdentifierDeclarations($clause->associatedIdentifierDeclarations());
		
		return $sql;
	}
	
	public function translateFromSubselectClause(Part\FromSubselectClause $clause)
	{
		$sql = 'FROM';
		
		$parts = array();
		foreach($clause->entityIdentifierDeclarations() as $eid) {
			
			$part = ' (SELECT ';
			
			$component = $this->_components[$eid->alias()];
			$mapping = $component['mapping'];
		
			$tableAlias = $this->_tableAliasFor($mapping->tableName(),$eid->alias());
			
			$beginning = true;
			foreach($mapping->columnNames() as $columnName) {
				if(!$beginning) {
					$part .= ', ';
				}else{
					$beginning = false;
				}

				$columnAlias = $this->_generateColumnAlias();

				$part .= $tableAlias.'.'.$columnName;
			}
		
			$part .= ' FROM '.$mapping->tableName().' '.$tableAlias;
			
			$conditionalAssociations = $this->_strategy->conditionalAssociations();
			foreach($clause->associatedIdentifierDeclarations() as $aid) {
				if(isset($conditionalAssociations[$aid->alias()])) {
					$part .= ' '.$aid->translate($this);
				}
			}
			
			if($whereClause = $clause->whereClause()) {
				$part .= ' '.$whereClause->translate($this);
			}
			
			if($limitClause = $clause->limitClause()) {
				$part .= ' '.$limitClause->translate($this);
			}
			
			$part .= ') '.$tableAlias;
			$parts[] = $part;
		}
		$sql .= ' '.implode(', ',$parts);
		
		$sql .=	' '.$this->translateAssociatedIdentifierDeclarations($clause->associatedIdentifierDeclarations());
		
		return $sql;
	}
	
	public function translateLimitClause(Part\LimitClause $limit)
	{	
		$sql = 'LIMIT';
		
		$translatedLimit = $limit->limit()->translate($this);
				
		if($offset = $limit->offset()) {
			$translatedOffset = $offset->translate($this);
		}else{
			$translatedOffset = null;
		}
	
		if($translatedOffset !== null) {
			$sql .= ' '.$translatedOffset.',';
		}
		$sql .= ' '.$translatedLimit;
		
		return $sql;
	}
	
	public function translateLiteralExpression(Part\LiteralExpression $literal)
	{
		switch($literal->type()) {
			case Part\LiteralExpression::L_STRING:
				return $this->quoteString($literal->value());
				
			case Part\LiteralExpression::L_NUMERIC:
				return $literal->value();
				
			case Part\LiteralExpression::L_BOOLEAN:
				$bool = (strtolower($literal->value()) == 'true') ? true : false;
				return $bool;
				
			default:
				return;
		}
	}
	
	public function translatePropertyExpression(Part\PropertyExpression $property)
	{
		$component = $this->_components[$property->entity()];
		$mapping = $component['mapping'];
		$tableAlias = $this->_tableAliasFor($mapping->tableName(),$property->entity());
		
		$columnName = $mapping->columnName($property->name());
		
		return $tableAlias.'.'.$columnName;
	}
	
	public function translateWhereClause(Part\WhereClause $where)
	{
		$sql = 'WHERE';
		
		$start = true;
		foreach($where->conditions() as $condition) {
			if(!$start) {
				$sql .= ($condition->bool() === true) ? ' &&' : ' ||';
			}else{
				$start = false;
			}
			$sql .= ' '.$condition->translate($this);
		}
		
		return $sql;
	}
}