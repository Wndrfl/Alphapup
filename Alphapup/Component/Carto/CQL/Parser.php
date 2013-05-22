<?php
namespace Alphapup\Component\Carto\CQL;

use Alphapup\Component\Carto\Carto;
use Alphapup\Component\Carto\CQL\CQLQuery;
use Alphapup\Component\Carto\CQL\Lexer;
use Alphapup\Component\Carto\CQL\Strategy;

class Parser
{
	private
		$_carto,
		$_components = array(),
		$_lexer,
		$_strategy;
		
	public function __construct(Carto $carto,Lexer $lexer,Strategy $strategy)
	{
		$this->_carto = $carto;
		$this->_lexer = $lexer;
		$this->_strategy = $strategy;
	}
	
	public function associatedIdentifierDeclaration()
	{
		$optional = false;
		if($this->_lexer->nextType() == Lexer::T_OPTIONAL) {
			$this->match(Lexer::T_OPTIONAL);
			$optional = true;
		}
		
		$this->match(Lexer::T_ASSOCIATED);
		
		$parentIdentifier = $this->entityIdentifier();
		
		// make sure association exists
		if(!isset($this->_components[$parentIdentifier])) {
			// DO EXCEPTION
			return;
		}
		$parentComponent = $this->_components[$parentIdentifier];
		$parentMapping = $parentComponent['mapping'];
		
		$this->match(Lexer::T_DOT);
		$this->match(Lexer::T_IDENTIFIER);
		
		$propertyName = $this->_lexer->currentValue();
		
		if(!$assocAnnot = $parentMapping->propertyAssociation($propertyName)) {
			// DO EXCEPTION
			return;
		}
		
		if(!$assocMapping = $this->_carto->mapping($assocAnnot['entity'])) {
			// DO EXCEPTION
			return;
		}
		
		if($this->_lexer->nextType() == Lexer::T_AS) {
			$this->match(Lexer::T_AS);
		}
		
		$entityIdentifier = $this->entityIdentifier();
		
		$component = array(
			'mapping' => $assocMapping,
			'parent' => $parentIdentifier,
			'associationType' => $assocAnnot['type'],
		);
		
		$this->_components[$entityIdentifier] = $component;
		
		return new Part\AssociatedIdentifierDeclaration($parentIdentifier,$propertyName,$entityIdentifier,$optional);
	}
	
	public function components()
	{
		return $this->_components;
	}
	
	public function createDirectComparisonExpression()
	{
		$bool = ($this->_lexer->currentType() == Lexer::T_OR) ? false : true;
		
		switch($this->_lexer->nextType()) {
			case Lexer::T_IDENTIFIER:
				$compare1 = $this->createPropertyExpression();
				break;
				
			case Lexer::T_PLACEHOLDER:
				$compare1 = $this->createPlaceholderExpression();
				break;
			
			default:
				$compare1 = $this->createLiteralExpression();
				break;
		}
		
		$operator = $this->createOperatorExpression();
		
		// do compare 2
		switch($this->_lexer->nextType()) {
			case Lexer::T_IDENTIFIER:
				$compare2 = $this->createPropertyExpression();
				break;
				
			case Lexer::T_PLACEHOLDER:
				$compare2 = $this->createPlaceholderExpression();
				break;
			
			default:
				$compare2 = $this->createLiteralExpression();
				break;
		}
		
		$comparison = new Part\DirectComparisonExpression($compare1,$operator,$compare2,$bool);
		return $comparison;
	}
	
	public function createFetchClause()
	{
		$isDistinct = false;
		if($this->_lexer->nextType() === Lexer::T_DISTINCT) {
			$this->match(Lexer::T_DISTINCT);
			$isDistinct = true;
		}
		
		$fetchExpressions[] = $this->createFetchExpression();
		
		while($this->_lexer->nextType() == Lexer::T_COMMA) {
			$this->match(Lexer::T_COMMA);
			$fetchExpressions[] = $this->createFetchExpression();
		}
		
		return new Part\FetchClause($fetchExpressions,$isDistinct);
	}

	public function createFetchExpression()
	{
		$peek = $this->_lexer->pointer();
		
		$alias = null;
		$supportsAlias = true;
		
		if($this->_lexer->nextType() == Lexer::T_IDENTIFIER) {
			$peek += 2;
			$this->_lexer->peekTo($peek);
			if($this->_lexer->peekType() == Lexer::T_DOT) {
				// Scalar
			}else{
				// Entity
				$supportsAlias = false;
				$expression = $this->entityIdentifier();
				
				$this->_components[$expression] = false;
			}
		}
		
		return new Part\FetchExpression($expression,$alias);
	}

	public function createFetchStatement()
	{
		$fetchClause = $this->createFetchClause();
		
		if($this->_strategy->type() == Strategy::QUALIFIER) {
			$stmt = new Part\FetchStatement($fetchClause,$this->createFromSubselectClause());
			
		}else{
		
			$stmt = new Part\FetchStatement($fetchClause,$this->createFromClause());
			if($this->_lexer->nextType() == Lexer::T_WHERE) {
				$stmt->setWhereClause($this->createWhereClause());
			}
		
			if($this->_lexer->nextType() == Lexer::T_LIMIT) {
				$stmt->setLimitClause($this->createLimitClause());
			}
		}
		
		return $stmt;
	}
	
	public function createFromClause()
	{	
		$this->match(Lexer::T_FROM);
		
		$identifierDeclarations = array();
		$identifierDeclarations[] = $this->entityIdentifierDeclaration();
		
		while($this->_lexer->nextType() == Lexer::T_COMMA) {
			$this->match(Lexer::T_COMMA);
			$identifierDeclarations[] = $this->entityIdentifierDeclaration();
		}
		
		$associatedIdentifierDeclarations = array();
		while($this->_lexer->nextTypeIs(array(Lexer::T_OPTIONAL,Lexer::T_ASSOCIATED))) {
			$associatedIdentifierDeclarations[] = $this->associatedIdentifierDeclaration();
		}
	
		return new Part\FromClause($identifierDeclarations,$associatedIdentifierDeclarations);
	}
	
	public function createFromSubselectClause()
	{
		$this->match(Lexer::T_FROM);
		
		$identifierDeclarations = array();
		$identifierDeclarations[] = $this->entityIdentifierDeclaration();
		
		while($this->_lexer->nextType() == Lexer::T_COMMA) {
			$this->match(Lexer::T_COMMA);
			$identifierDeclarations[] = $this->entityIdentifierDeclaration();
		}
		
		$associatedIdentifierDeclarations = array();
		while($this->_lexer->nextTypeIs(array(Lexer::T_OPTIONAL,Lexer::T_ASSOCIATED))) {
			$associatedIdentifierDeclarations[] = $this->associatedIdentifierDeclaration();
		}
		
		$subselect = new Part\FromSubselectClause($identifierDeclarations,$associatedIdentifierDeclarations);
		
		if($this->_lexer->nextType() == Lexer::T_WHERE) {
			$subselect->setWhereClause($this->createWhereClause());
		}
		
		if($this->_lexer->nextType() == Lexer::T_LIMIT) {
			$subselect->setLimitClause($this->createLimitClause());
		}
		
		return $subselect;
	}

	public function createLimitClause()
	{
		$this->match(Lexer::T_LIMIT);
		
		$limit = $this->createLiteralExpression();
		
		$offset = null;
		if($this->_lexer->nextType() == Lexer::T_COMMA) {
			$this->match(Lexer::T_COMMA);
			$offset = $this->createLiteralExpression();
		}
		
		return new Part\LimitClause($limit,$offset);
	}
	
	public function createLiteralExpression()
	{
		switch($this->_lexer->nextType()) {
			case Lexer::T_STRING:
				$this->match(Lexer::T_STRING);
				$literal = new Part\LiteralExpression(Part\LiteralExpression::L_STRING,$this->_lexer->currentValue());
				break;
				
			case Lexer::T_INTEGER:
			case Lexer::T_FLOAT:
				$this->match(
                    $this->_lexer->isNextType(Lexer::T_INTEGER) ? Lexer::T_INTEGER : Lexer::T_FLOAT
                );
				$literal = new Part\LiteralExpression(Part\LiteralExpression::L_NUMERIC,$this->_lexer->currentValue());
				break;
				
		    case Lexer::T_TRUE:
            case Lexer::T_FALSE:
                $this->match(
                    $this->_lexer->isNextType(Lexer::T_TRUE) ? Lexer::T_TRUE : Lexer::T_FALSE
                );
                $literal = new Part\LiteralExpression(Part\LiteralExpression::L_BOOLEAN, $this->_lexer->currentValue());

			default:
				// DO EXCEPTION
				return false;
		}
		
		return $literal;
	}
	
	public function createOperatorExpression()
	{
		switch($this->_lexer->nextType()) {
			case Lexer::T_EQUALS:
				$this->match(Lexer::T_EQUALS);
				return new Part\OperatorExpression(Part\OperatorExpression::O_EQUAL_TO);
				break;
				
			case Lexer::T_NEGATE:
				$this->match(Lexer::T_NEGATE);
				$this->match(Lexer::T_EQUALS);
				return new Part\OperatorExpression(Part\OperatorExpression::O_NOT_EQUAL_TO);
				break;
				
			case Lexer::T_GREATER_THAN:
				$this->match(Lexer::T_GREATER_THAN);
				
				if($this->_lexer->nextType() == Lexer::T_EQUALS) {
					$this->match(Lexer::T_EQUALS);
					return new Part\OperatorExpression(Part\OperatorExpression::O_GREATER_THAN_OR_EQUAL_TO);
					break;
				}else{
					return new Part\OperatorExpression(Part\OperatorExpression::O_GREATER_THAN);
					break;
				}
				break;
				
			case Lexer::T_LESS_THAN:
				$this->match(Lexer::T_LESS_THAN);

				if($this->_lexer->nextType() == Lexer::T_EQUALS) {
					$this->match(Lexer::T_EQUALS);
					return new Part\OperatorExpression(Part\OperatorExpression::O_LESS_THAN_OR_EQUAL_TO);
					break;
				}else{
					return new Part\OperatorExpression(Part\OperatorExpression::O_LESS_THAN);
					break;
				}
				break;
				
		}
	}
	
	public function createPlaceholderExpression()
	{
		$this->match(Lexer::T_PLACEHOLDER);
		
		$current = $this->_lexer->currentValue();
		
		if($current[0] == '?') {
			
			$param = $current[0];
			$localParam = substr($current,1);
			if($localParam == '') {
				$localParam = $this->_paramPointer++;
			}
			
		}elseif($current[0] == ':') {
			
			$param = $localParam = substr($current,1);
		}elseif($current[0] == '{') {
			
			$param = $localParam = substr($current,2,strlen($current)-4);
			
		}else{
			// DO EXCEPTION
			return false;
		}
		
		if(isset($this->_params[$localParam])) {
			$value = $this->_params[$localParam];
		}else{
			// DO EXCEPTION
			return false;
		}
		
		$placeholder = new Part\PlaceholderExpression($param,$value);
		
		return $placeholder;
	}
	
	public function createPropertyExpression()
	{
		// properties start with entity indicator
		$entity = $this->entityIdentifier();
		
		$this->match(Lexer::T_DOT);
		$this->match(Lexer::T_IDENTIFIER);
		
		$property = new Part\PropertyExpression($this->_lexer->currentValue(),$entity);
		return $property;
	}
	
	public function entityIdentifier()
	{
		$this->match(Lexer::T_IDENTIFIER);
		return $this->_lexer->currentValue();
	}
	
	public function entityIdentifierDeclaration()
	{
		$entityName = $this->entityName();
		
		if($this->_lexer->nextType() == Lexer::T_AS) {
			$this->match(Lexer::T_AS);
		}
		
		$entityIdentifier = $this->entityIdentifier();
		
		$component = array(
			'mapping' => $this->_carto->mapping($entityName),
			'parent' => null,
			'associationType' => null,
		);
		
		$this->_components[$entityIdentifier] = $component;
		
		return new Part\EntityIdentifierDeclaration($entityName,$entityIdentifier);
	}
	
	public function createConditionExpression()
	{
		if($this->_lexer->nextType() == Lexer::T_IDENTIFIER) {
		
			// if comparison
			$glimpse = 2;
			$type = $this->_lexer->aheadType($glimpse);
			while($type == Lexer::T_DOT || $type == Lexer::T_IDENTIFIER) {
				$glimpse++;
				$type = $this->_lexer->aheadType($glimpse);
			}
		
			switch($type) {
				
				// between
				case Lexer::T_BETWEEN:
					break;
				
				// in statement
				case Lexer::T_IN:
					break;
				
				// direct comparison
				default:
					return $this->createDirectComparisonExpression();
					break;
					
			}
		}
	}
	
	
	public function createWhereClause()
	{
		$this->match(Lexer::T_WHERE);
		
		$conditions = array();
		
		if($this->_lexer->nextTypeIs(array(Lexer::T_IDENTIFIER))) { 
			$conditions[] = $this->createConditionExpression();
		}elseif($this->_lexer->nextType() == Lexer::T_OPEN_PARENTHESES) {
			$conditions[] = $this->createNestedConditionExpression();
		}
		
		while($this->_lexer->nextTypeIs(array(Lexer::T_AND,Lexer::T_OR))) {
			
			$this->match(array(Lexer::T_AND,Lexer::T_OR));
			
			if($this->_lexer->nextTypeIs(array(Lexer::T_IDENTIFIER))) { 
				$conditions[] = $this->createConditionExpression();
			}elseif($this->_lexer->nextType() == Lexer::T_OPEN_PARENTHESES) {
				$conditions[] = $this->createNestedConditionExpression();
			}
		}
		
		$where = new Part\WhereClause($conditions);
		return $where;
	}
	
	public function entityName()
	{
		$this->match(Lexer::T_IDENTIFIER);
		return $this->_lexer->currentValue();
	}
	
	public function match($token)
	{
		if(is_array($token)) {
			if(!in_array($this->_lexer->nextType(),$token)) {
				// DO EXCEPTION
				die('cql syntax error, expecting '.$token);
				return false;
			}
		}else{
			if($this->_lexer->nextType() != $token) {
				// DO EXCEPTION
				die('cql syntax error, expecting '.$token);
				return false;
			}
		}
		$this->_lexer->onToNext();
	}
	
	public function parse()
	{	
		$this->_lexer->resetPointer();
		switch($this->_lexer->currentType()) {
			case Lexer::T_FETCH:
				$statement = $this->createFetchStatement();
				break;
		}
		
		return new ParseResult($statement,$this->_components);
	}
}