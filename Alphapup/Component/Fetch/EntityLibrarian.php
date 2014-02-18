<?php
namespace Alphapup\Component\Fetch;

use Alphapup\Component\Dexter\Dexter;
use Alphapup\Component\Fetch\EntityMapper;
use Alphapup\Component\Fetch\Fetch;
use Alphapup\Component\Fetch\Hydrator;
use Alphapup\Component\Fetch\PublicLibrary;
use Alphapup\Component\Fetch\ResultMapper;
use Alphapup\Component\Fetch\SQL\SQLBuilder;
use Alphapup\Component\Fetch\SQL\Expr\Join;

/**
 * An EntityLibrarian is a tool that knows how to "search"
 * for a single type of Entity.
 * 
 * It is passed an EntityMapper, which gives it information
 * about how to find the Entity it has been designated.
 */
class EntityLibrarian
{
	private
		$_dexter,
		$_entityMapper,
		$_fetch,
		$_publicLibrary;
		
	// These need to be reset after each query
	private	
		$_queryColumnAliases = array(),
		$_queryTableAliases = array();
		
	public function __construct(Fetch $fetch,EntityMapper $entityMapper, Dexter $dexter, PublicLibrary $publicLibrary)
	{
		$this->_fetch = $fetch;
		$this->_entityMapper = $entityMapper;
		$this->_dexter = $dexter;
		$this->_publicLibrary = $publicLibrary;
	}
	
	public function _executeQuery(array $cond=array(),$orderBy=null,$limit=null,$offset=null)
	{
		// Do this so that we can generate new aliases, etc
		$this->_resetQueryTools();
		
		// Start building a query
		$qb = new SQLBuilder();
		$rm = new ResultMapper();
		
		// EntityNames are changed to aliases in queries,
		// so, we need to tell the resultMapper which aliases
		// belong to which entities
		$localTableAlias = $this->_generateQueryTableAlias($this->_entityMapper->entityName());
		$rm->mapTableAliasToClassName($localTableAlias,$this->_entityMapper->entityFullName());
		
		// Get column names
		$columns = array();
		foreach($this->_entityMapper->columnNames() as $columnName) {
			
			// generate a fresh alias for this column to be used in the query
			$columnAlias = $this->_generateColumnNameAlias($localTableAlias);
			
			// add to columns
			$columns[] = $qb->expr()->column($localTableAlias,$columnName,$columnAlias);
			
			// add to mapping
			$rm->mapPropertyNameToColumnName(
				$this->_entityMapper->entityFullName(),
				$this->_entityMapper->propertyNameForColumn($columnName),
				$columnAlias
			);
		}
		
		// JOINS
		// Make sure we have any association required columns
		// We do this here because columns like "account_id" were not
		// marked as normal properties in the entity, and therefore
		// were not added above
		foreach($this->_entityMapper->associations() as $assoc) {
			
			// ONE TO ONE
			if($assoc['type'] == EntityMapper::ONE_TO_ONE) {
				
				// the local column of the local entity is being used, and therefore
				// we need to include it in the query
				if(isset($assoc['local'])) {
				
					// generate a fresh alias for this column to be used in the query
					$columnAlias = $this->_generateColumnNameAlias($localTableAlias);
				
					// add to columns
					$columns[] = $qb->expr()->column($localTableAlias,$assoc['local'],$columnAlias);
				
					// add to mapping
					$rm->mapMeta(
						$this->_entityMapper->entityFullName(),
						$assoc['local'],
						$columnAlias
					);
				}
				
				// one to one join
				// gather all foreign entity things
				if(isset($assoc['entity']) && !$assoc['lazy']) {
					
					// get the mapping, etc for this entity
					$foreignClassName = $this->_fetch->className($assoc['entity']);
					$foreignEntityAlias = $this->_fetch->entityAlias($foreignClassName);
					$foreignEntityMapper = $this->_fetch->entityMapper($foreignEntityAlias);
					
					// create foreign table alias for this entity
					$foreignTableAlias = $this->_generateQueryTableAlias($foreignEntityMapper->entityName());
					$rm->mapTableAliasToClassName($foreignTableAlias,$foreignEntityMapper->entityFullName());
					
					// record this entity as a child
					$rm->mapForeignTableAliasAsChild($foreignTableAlias,$localTableAlias);
					
					// make the join
					$qb->leftJoin(
						$foreignEntityMapper->tableName(),
						$foreignTableAlias,
						Join::CONDITION_TYPE_ON,
						array(
							$qb->expr()->isEqualTo(
								$qb->expr()->column($localTableAlias,$assoc['local']),
								$qb->expr()->column($foreignTableAlias,$assoc['foreign'])
							)
						)
					);
					
					// gather all foreign columns
					foreach($foreignEntityMapper->columnNames() as $columnName) {

						// generate a fresh alias for this column to be used in the query
						$columnAlias = $this->_generateColumnNameAlias($foreignTableAlias);

						// add to columns
						$columns[] = $qb->expr()->column($foreignTableAlias,$columnName,$columnAlias);

						// add to mapping
						$rm->mapPropertyNameToColumnName(
							$foreignEntityMapper->entityFullName(),
							$foreignEntityMapper->propertyNameForColumn($columnName),
							$columnAlias
						);
					}
				}
				
			}
		}
		
		// Build SELECT
		$qb->select($columns);
		
		// Build FROM
		$qb->from($this->_entityMapper->tableName(),$localTableAlias);
		
		// Loop thru conditions
		$params = array();
		if($cond) {
			
			$conditions = array();
			
			foreach($cond as $propertyName => $value) {
				/**
				 * Appended comparison instruction to the propertyName?
				 * example: 'user_id ='
				 */
				$propertyNameParts = explode(' ',$propertyName);
				if(isset($propertyNameParts[1])) {
					
					$propertyName = $propertyNameParts[0];
					
					switch(strtolower($propertyNameParts[1])) {	
						
						// IN comparison						
						case 'in':
							if(is_array($value)) {
								$placeholders = array();
								foreach($value as $v) {
									$placeholders[] = '?';
									$params[] = $v;
								}
								$placeholder = '('.implode(',',$placeholders).')';
							}else{
								$params[] = $value;
								$placeholder = '?';
							}
							$conditions[] = $qb->expr()->isIn(
								$qb->expr()->column(
									$this->_entityMapper->tableName(),
									$this->_entityMapper->columnName($propertyName)
								),
								$placeholder
							);
							break;
						
						// EQUALS comparison	
						case '=':
						default:
							$conditions[] = $qb->expr()->isEqualTo(
								$qb->expr()->column(
									$this->_entityMapper->tableName(),
									$this->_entityMapper->columnName($propertyName)
								),
								'?'
							);
							$params[] = $value;
							break;
					}
			
				// If there is a column that corresponds to the supplied propertyName
				}elseif($columnName = $this->_entityMapper->columnNameForProperty($propertyName)) {
					
					$conditions[] = $qb->expr()->isEqualTo(
						$qb->expr()->column(
							$localTableAlias,
							$this->_entityMapper->columnNameForProperty($propertyName)
						),
						'?'
					);
					$params[] = $value;
				
				// If the property has an association to another entity
				}elseif($assoc = $this->_entityMapper->associationForProperty($propertyName)) {
					$conditions[] = $qb->expr()->isEqualTo(
						$qb->expr()->column($this->_entityMapper->tableName(),$assoc['local']),
						'?'
					);
					$params[] = $value;
				}
			}
			
			// Insert conditions into query
			if($conditions) {
				$qb->where($conditions);
			}
		}
		
		// Check for order by statements
		if(is_array($orderBy)) {
			foreach($orderBy as $propertyName => $dir) {
				$col = $this->_entityMapper->columnName($propertyName);
				$qb->orderBy($col,$dir);
			}
		}

		// Check for limit statements
		if(!is_null($limit) || !is_null($offset)) {
			$qb->limit($limit,$offset);
		}
		
		// Gather raw results
		$sql = $qb->sql();
		echo $sql.'<br /><br />';
		
		$ttl = (isset($options['ttl'])) ? intval($options['ttl']) : 0;
		$rows = $this->_dexter->execute($sql,$params,$ttl)->results();
		
		return array(
			'resultMapping' => $rm,
			'rows' => $rows
		);
	}

	/**
	 * Column aliases are generated by appending to the table alias:
	 * a11,a12,a13...etc
	 */
	public function _generateColumnNameAlias($queryTableAlias)
	{
		if(!isset($this->_columnNameAliasCounters[$queryTableAlias])) {
			$this->_columnNameAliasCounters[$queryTableAlias] = 0;
		}
		return $queryTableAlias.(++$this->_columnNameAliasCounters[$queryTableAlias]);
	}
	
	public function _generateQueryTableAlias($entityName)
	{
		$c = 1;
		$i = strtolower(substr($entityName,0,1));
		while(in_array($i.$c,$this->_queryTableAliases)) {
			$c++;
		}
		$this->_queryTableAliases[] = $i.$c;
		return $i.$c;
	}
	
	public function _resetQueryTools()
	{
		$this->_columnNameAliasCounters[] = array();
		$this->_queryTableAliases = array();
	}
	
	/**
	 * Generic search
	 */
	public function fetchBy(array $cond=array(),$orderBy=null,$limit=null,$offset=null,array $options=array())
	{
		
		$result = $this->_executeQuery($cond,$orderBy,$limit,$offset);
		
		// If no rows, nix
		if(!isset($result['rows'][0])) {
			return false;
		}
		
		// Hydrate entities w/ the results
		$hydrator = new Hydrator($this->_fetch,$this->_publicLibrary,$result['resultMapping']);
		$results = $hydrator->hydrate($result['rows']);
		
		return $results;
	}
	
	public function fetchOne(array $cond=array(),array $options=array())
	{
		$result = $this->_executeQuery($cond,null,1,null);
		
		// If no rows, nix
		if(!isset($result['rows'][0])) {
			return false;
		}
		
		// Are we being requested to fill a specific instance
		// of an entity?
		$fillEntity = null;
		$className = $this->_entityMapper->entityFullName();
		if(isset($options['useEntity']) && $options['useEntity'] instanceof $className) {
			$fillEntity = $options['useEntity'];
		}
		
		// Hydrate entity w/ the results
		$hydrator = new Hydrator($this->_fetch,$this->_publicLibrary,$result['resultMapping']);
		$results = $hydrator->hydrateRow($result['rows'][0],$fillEntity);
		
		if($results[0]) {
			return $results[0];
		}
		
		return false;
	}
}