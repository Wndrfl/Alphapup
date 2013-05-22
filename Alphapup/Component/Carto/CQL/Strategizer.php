<?php
namespace Alphapup\Component\Carto\CQL;

use Alphapup\Component\Carto\Mapping;
use Alphapup\Component\Carto\CQL\ParseResult;
use Alphapup\Component\Carto\CQL\Strategy;

class Strategizer
{		
	private
		$_carto,
		$_lexer;
		
	private
		$_associatedEntityIdentifiers,
		$_associatedParentProperties,
		$_associationsAreFetched,
		$_conditionalAssociationIdentifiers,
		$_conditionalEntityIdentifiers,
		$_fetchedAssociatedEntityIdentifiers,
		$_fetchedEntities,
		$_hasLimitClause,
		$_hasWhereClause,
		$_rootEntities,
		$_toManyAssociationsAreFetched;
		
	public function __construct($carto,$lexer) {
		$this->_carto = $carto;
		$this->_lexer = $lexer;
	}
		
	public function associationsAreFetched()
	{
		if(!empty($this->_associationsAreFetched)) {
			return $this->_associationsAreFetched;
		}
		
		$this->_associationsAreFetched = false;
		
		$fetchedEntities = $this->fetchedEntities();
		$rootEntities = $this->rootEntities();
		foreach($fetchedEntities as $entityIdentifier) {
			if(!isset($rootEntities[$entityIdentifier])) {
				$this->_associationsAreFetched = true;
				break;
			}
		}
		
		return $this->_associationsAreFetched;
	}
	
	public function associatedEntityIdentifiers()
	{
		
	}
	
	public function conditionalAssociationIdentifiers()
	{
		if(!empty($this->_conditionalAssociationIdentifiers)) {
			return $this->_conditionalAssociationIdentifiers;
		}
		
		$this->_conditionalAssociationIdentifiers = array();
		
		$rootEntities = $this->rootEntities();
		
		foreach($this->conditionalEntityIdentifiers() as $entityIdentifier) {
			if(!isset($rootEntities[$entityIdentifier])) {
				$this->_conditionalAssociationIdentifiers[$entityIdentifier] = $entityIdentifier;
			}
		}
		
		return $this->_conditionalAssociationIdentifiers;
	}
	
	public function conditionalEntityIdentifiers()
	{
		if(!empty($this->_conditionalEntityIdentifiers)) {
			return $this->_conditionalEntityIdentifiers;
		}

		$this->_lexer->resetPointer();
		$this->_conditionalEntityIdentifiers = array();

		if($this->_lexer->goUntil(Lexer::T_WHERE)) {
			while(
				$this->_lexer->currentType() !== null
				&& $this->_lexer->currentType() != Lexer::T_ORDER
				&& $this->_lexer->currentType() != Lexer::T_LIMIT) {
				if($this->_lexer->currentType() == Lexer::T_IDENTIFIER) {
					$this->_conditionalEntityIdentifiers[$this->_lexer->currentValue()] = $this->_lexer->currentValue();
					$this->_lexer->matchNext(Lexer::T_DOT);
					$this->_lexer->matchNext(Lexer::T_IDENTIFIER);
				}
				$this->_lexer->onToNext();
			}
		}

		return $this->_conditionalEntityIdentifiers;
	}
		
	public function createStrategy()
	{	
		$strategy = new Strategy();
			
		// if no associations are being fetched,
		// we can just run a simple query
		if(!$this->associationsAreFetched()) {
			return $strategy;
		}
		
		// if associations ARE being fetched,
		// we only need to go more complex if a
		// TO_MANY association is being fetched
		if(!$this->toManyAssociationsAreFetched()) {
			return $strategy;
		}
		
		// we only need more complex query if
		// a WHERE clause or LIMIT clause is
		// supplied
		if($this->hasWhereClause() || $this->hasLimitClause()) {
			$strategy->setType(Strategy::QUALIFIER);
		}
		
		if($strategy->type() == Strategy::QUALIFIER) {
			// we need to strategize which joins
			// to include in the qualifying derived table
			$strategy->setConditionalAssociations($this->conditionalAssociationIdentifiers());
		}
		
		return $strategy;
	}
	
	public function fetchedAssociatedEntityIdentifiers()
	{
		if(!empty($this->_fetchedAssociatedEntityIdentifiers)) {
			return $this->_fetchedAssociatedEntityIdentifiers;
		}
		
		$this->_lexer->resetPointer();
		$this->_fetchedAssociatedEntityIdentifiers = array();
		
		if($this->_lexer->goUntil(Lexer::T_ASSOCIATED)) {
			
			$this->_lexer->matchNext(Lexer::T_IDENTIFIER);
			$parentEntityIdentifier = $this->_lexer->currentValue();
			
			$this->_lexer->matchNext(Lexer::T_DOT);
			$this->_lexer->matchNext(Lexer::T_IDENTIFIER);
			$parentPropertyName = $this->_lexer->currentValue();
			
			if($this->_lexer->nextType() == Lexer::T_AS) {
				$this->_matchNext(Lexer::T_AS);
			}
			
			$this->_lexer->matchNext(Lexer::T_IDENTIFIER);
			$entityIdentifier = $this->_lexer->currentValue();
			
			$this->_fetchedAssociatedEntityIdentifiers[$entityIdentifier] = $parentEntityIdentifier;
			$this->_associatedParentProperties[$entityIdentifier] = $parentPropertyName;
			
			while($this->_lexer->nextType() == Lexer::T_ASSOCIATED) {
				$this->_lexer->matchNext(Lexer::T_ASSOCIATED);
				$this->_lexer->matchNext(Lexer::T_IDENTIFIER);
				$parentEntityIdentifier = $this->_lexer->currentValue();

				$this->_lexer->matchNext(Lexer::T_DOT);
				$this->_lexer->matchNext(Lexer::T_IDENTIFIER);
				$parentPropertyName = $this->_lexer->currentValue();
				
				if($this->_lexer->nextType() == Lexer::T_AS) {
					$this->_matchNext(Lexer::T_AS);
				}

				$this->_lexer->matchNext(Lexer::T_IDENTIFIER);
				$entityIdentifier = $this->_lexer->currentValue();

				$this->_fetchedAssociatedEntityIdentifiers[$entityIdentifier] = $parentEntityIdentifier;
				$this->_associatedParentProperties[$entityIdentifier] = $parentPropertyName;
			}
		}
		
		return $this->_fetchedAssociatedEntityIdentifiers;
	}
	
	public function fetchedEntities()
	{
		if(!empty($this->_fetchedEntities)) {
			return $this->_fetchedEntities;
		}
		
		$this->_lexer->resetPointer();
		$this->_fetchedEntities = array();
		
		if($this->_lexer->goUntil(Lexer::T_IDENTIFIER)) {
			
			$entityIdentifier = $this->_lexer->currentValue();
			
			if($this->_lexer->nextType() == Lexer::T_DOT) {
				$this->_lexer->matchNext(Lexer::T_DOT);
			}
			
			if($this->_lexer->nextType() == Lexer::T_IDENTIFIER) {
				$this->_lexer->matchNext(Lexer::T_IDENTIFIER);
			}
			
			$this->_fetchedEntities[$entityIdentifier] = $entityIdentifier;
			
			while($this->_lexer->nextType() == Lexer::T_COMMA) {
				
				$this->_lexer->matchNext(Lexer::T_COMMA);
				$this->_lexer->matchNext(Lexer::T_IDENTIFIER);

				$entityIdentifier = $this->_lexer->currentValue();

				if($this->_lexer->nextType() == Lexer::T_DOT) {
					$this->_lexer->matchNext(Lexer::T_DOT);
				}
				if($this->_lexer->nextType() == Lexer::T_IDENTIFIER) {
					$this->_lexer->matchNext(Lexer::T_IDENTIFIER);
				}

				$this->_fetchedEntities[$entityIdentifier] = $entityIdentifier;
			}
		}
		
		return $this->_fetchedEntities;
	}

	public function hasLimitClause()
	{
		if(!empty($this->_hasLimitClause)) {
			return $this->_hasLimitClause;
		}
		
		$this->_lexer->resetPointer();
		
		$this->_hasLimitClause = false;
		if($this->_lexer->goUntil(Lexer::T_LIMIT)) {
			$this->_hasLimitClause = true;
		}
		
		return $this->_hasLimitClause;
	}
	
	public function hasWhereClause()
	{
		if(!empty($this->_hasWhereClause)) {
			return $this->_hasWhereClause;
		}
		
		$this->_lexer->resetPointer();
		
		$this->_hasWhereClause = false;
		if($this->_lexer->goUntil(Lexer::T_WHERE)) {
			$this->_hasWhereClause = true;
		}
		
		return $this->_hasWhereClause;
	}
	
	public function rootEntities()
	{
		if(!empty($this->_rootEntities)) {
			return $this->_rootEntities;
		}
		
		$this->_lexer->resetPointer();
		$this->_rootEntities = array();
		
		if($this->_lexer->goUntil(Lexer::T_FROM)) {
			
			$this->_lexer->matchNext(Lexer::T_IDENTIFIER);
			$entityName = $this->_lexer->currentValue();
			
			if($this->_lexer->nextType() == Lexer::T_AS) {
				$this->_lexer->matchNext(Lexer::T_AS);
			}
			
			$this->_lexer->matchNext(Lexer::T_IDENTIFIER);
			$entityIdentifier = $this->_lexer->currentValue();
			
			$this->_rootEntities[$entityIdentifier] = $entityName;
			
			while($this->_lexer->nextType() == Lexer::T_COMMA) {
				$this->_lexer->matchNext(Lexer::T_COMMA);
				$this->_lexer->matchNext(Lexer::T_IDENTIFIER);
				$entityName = $this->_lexer->currentValue();

				if($this->_lexer->nextType() == Lexer::T_AS) {
					$this->_matchNext(Lexer::T_AS);
				}

				$this->_lexer->matchNext(Lexer::T_IDENTIFIER);
				$entityIdentifier = $this->_lexer->currentValue();

				$this->_rootEntities[$entityIdentifier] = $entityName;
			}
		}
		
		return $this->_rootEntities;
	}
	
	public function toManyAssociationsAreFetched()
	{
		if(!empty($this->_toManyAssociationsAreFetched)) {
			return $this->_toManyAssociationsAreFetched;
		}
		
		$this->_toManyAssociationsAreFetched = false;
		foreach($this->fetchedAssociatedEntityIdentifiers() as $entityIdentifier => $parentIdentifier) {
			$parentEntityName = $this->_rootEntities[$parentIdentifier];
			$parentMapping = $this->_carto->mapping($parentEntityName);
			$assocAnnot = $parentMapping->propertyAssociation($this->_associatedParentProperties[$entityIdentifier]);
			
			if($assocAnnot['type'] == Mapping::ONE_TO_MANY || $assocAnnot['type'] == Mapping::MANY_TO_MANY) {
				$this->_toManyAssociationsAreFetched = true;
				break;
			}
		}
		
		return $this->_toManyAssociationsAreFetched;
	}
}