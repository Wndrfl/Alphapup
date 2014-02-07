<?php
namespace Alphapup\Component\Fetch;

use Alphapup\Component\Dexter\Dexter;
use Alphapup\Component\Fetch\EntityMapper;
use Alphapup\Component\Fetch\Hydrator;
use Alphapup\Component\Fetch\PublicLibrary;
use Alphapup\Component\Fetch\SQL\SQLBuilder;

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
		$_publicLibrary;
		
	public function __construct(EntityMapper $entityMapper, Dexter $dexter, PublicLibrary $publicLibrary)
	{
		$this->_entityMapper = $entityMapper;
		$this->_dexter = $dexter;
		$this->_publicLibrary = $publicLibrary;
	}
	
	/**
	 * Generic search
	 */
	public function fetchBy(array $cond=array(),$orderBy=null,$limit=null,$offset=null,array $options=array())
	{
		// Start building a query
		$qb = new SQLBuilder();
		
		// Get column names
		$columns = array();
		foreach($this->_entityMapper->columnNames() as $columnName) {
			$columns[] = $qb->expr()->column($this->_entityMapper->tableName(),$columnName);
		}
		
		// Build SELECT
		$qb->select($columns);
		
		// Build FROM
		$qb->from($this->_entityMapper->tableName());
		
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
				}elseif($columnName = $this->_entityMapper->columnName($propertyName)) {
					
					$conditions[] = $qb->expr()->isEqualTo(
						$qb->expr()->column(
							$this->_entityMapper->tableName(),
							$this->_entityMapper->columnName($propertyName)
						),
						'?'
					);
					$params[] = $value;
				
				// If the property has an association to another entity
				/*
				}elseif($assoc = $this->_mapping->propertyAssociation($propertyName)) {
					$conditions[] = $qb->expr()->isEqualTo(
						$qb->expr()->column($this->_mapping->tableName(),$assoc['local']),
						'?'
					);
					$params[] = $value;
					*/
				}
			}
			
			// Insert conditions into query
			$qb->where($conditions);
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
		
		$ttl = (isset($options['ttl'])) ? intval($options['ttl']) : 0;
		$rows = $this->_dexter->execute($sql,$params,$ttl)->results();
		
		// If no rows, nix
		if(!isset($rows[0])) {
			return false;
		}
		
		print_r($rows);
		
		// Hydrate entities w/ the results
		$hydrator = new Hydrator($this->_publicLibrary,$this->_entityMapper);
		$results = $hydrator->hydrate($rows);
		
		return $results;
	}
}