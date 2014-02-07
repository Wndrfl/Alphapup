<?php
namespace Alphapup\Component\Fetch\SQL;

use Alphapup\Component\Fetch\SQL\ExprBuilder;

class SQLBuilder
{
	const
		Q_TYPE_SELECT		= 1;
		
	private
		$_expr,
		$_parts = array(
			'distinct' => false,
			'from' => array(),
			'join' => array(),
			'limit' => false,
			'orderBy' => array(),
			'select' => array(),
			'where' => array(),
		),
		$_type = null;
	
	public function __construct()
	{
		$this->_expr = new ExprBuilder();
	}
	
	private function _generateQueryPart($partType,$pre='',$separator='',$empty='')
	{
		$queryPart = $this->part($partType);
		
        if(!$queryPart) {
            return (!is_null($empty) ? $empty : '');
        }
        
		$sql = $pre;
		if(is_array($queryPart)) {
			foreach($queryPart as $part) {
				$sql .= (is_array($part)) ? implode($separator, $part) : $part;
			}
		}else{
        	$sql .= (is_array($queryPart)) ? implode($separator, $queryPart) : $queryPart;
		}
		return $sql;
	}
		
	private function _generateSelectSql()
	{
		$sql = 'SELECT';
		if($this->_parts['distinct'] == true) {
			$sql .= ' DISTINCT';
		}
		
		$sql .= $this->_generateQueryPart('select',' ',', ','');
		
		$fromParts = $this->part('from');
		
		$joinParts = $this->part('join');
		
        $fromClauses = array();

        // Loop through all FROM clauses
        if(!empty($fromParts)) {
            $sql .= ' FROM ';

            foreach ($fromParts as $from) {
                $fromClause = (string) $from;

                if($from instanceof Expr\From && isset($joinParts[$from->alias()])) {
                    foreach($joinParts[$from->alias()] as $join) {
                        $fromClause .= ' ' . ((string) $join);
                    }
                }

                $fromClauses[] = $fromClause;
            }
        }

		$sql .= implode(', ',$fromClauses);
		$sql .= $this->_generateQueryPart('join',' ',' ');
		$sql .= $this->_generateQueryPart('where',' WHERE ');
		$sql .= $this->_generateQueryPart('orderBy',' ORDER BY ');
		$sql .= $this->_generateQueryPart('limit',' LIMIT ');
		
		return $sql;
	}
		
	public function add($partType,$part,$overwrite=true)
	{
		$isMultiple = is_array($this->_parts[$partType]);
		
		if(!$overwrite && $isMultiple) {
			if(is_array($part)) {
				$key = key($part);
                
                $this->_parts[$partType][$key][] = $part[$key];
			}else{
				$this->_parts[$partType][] = $part;
			}
		}else{
			$this->_parts[$partType] = ($isMultiple) ? array($part) : $part;
		}
		
		return $this;
	}
	
	public function expr()
	{
		return $this->_expr;
	}
	
	public function from($table,$alias=null)
	{
		if($table instanceof self) {
			$table = '('.$table->sql().')';
		}
		
		return $this->add('from',new Expr\From($table,$alias),false);
	}
	
	public function innerJoin($table,$alias,$conditionType,$conditions=array())
	{
		return $this->join(Expr\Join::TYPE_INNER,$table,$alias,$conditionType,$conditions);
	}
	
	public function insert(array $columns=array(),$table)
	{
		$this->_type = self::Q_TYPE_INSERT;
		
		return $this->add('insert',Expr\Insert($columns,$table));
	}
	
	public function join($type,$table,$alias,$conditionType,$conditions=array())
	{
		return $this->add('join',
				new Expr\Join($type,$table,$alias,$conditionType,$conditions),
				false);
	}
	
	public function leftJoin($table,$alias,$conditionType,$conditions=array())
	{
		return $this->join(Expr\Join::TYPE_LEFT,$table,$alias,$conditionType,$conditions);
	}
	
	public function limit($limit,$offset=null)
	{
		return $this->add('limit',new Expr\Limit($limit,$offset),true);
	}
	
	public function orderBy($column,$direction='ASC')
	{
		return $this->add('orderBy',new Expr\OrderBy($column,$direction));
	}
	
	public function part($partType)
	{
		return (isset($this->_parts[$partType])) ? $this->_parts[$partType] : '';
	}
	
	public function select($columns=null)
	{
		$this->_type = self::Q_TYPE_SELECT;
		
		return $this->add('select',$this->expr()->select($columns),true);
	}
	
	public function sql()
	{
		return $this->_generateSelectSql();
	}
	
	public function where($conditions)
	{
		return $this->add('where',$conditions,true);
	}
}