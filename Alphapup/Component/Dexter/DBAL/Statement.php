<?php
namespace Alphapup\Component\Dexter\DBAL;

use Alphapup\Component\Dexter\Dexter;
use Alphapup\Component\Dexter\Query;
use Alphapup\Component\Dexter\DBAL\Connection;

class Statement
{
	private
		$_dexter,
		$_sql,
		$_stmt;
		
	public function __construct(Dexter $dexter,$sql,\PDOStatement $stmt)
	{
		$this->_dexter = $dexter;
		$this->_sql = $sql;
		$this->_stmt = $stmt;
	}
	
	public function bindValue($bindParam,$value,$type=null)
	{
		$type = (!is_null($type)) ? $type : $this->determineValueType($value);
		
		$this->_stmt->bindValue($bindParam,$value,$type);
		
		return $this;
	}
	
	public function closeCursor()
	{
		$this->_stmt->closeCursor();
	}
	
	public function determineValueType($value)
	{
		if(is_int($value))
            $type = \PDO::PARAM_INT;
        elseif(is_bool($value))
            $type = \PDO::PARAM_BOOL;
        elseif(is_null($value))
            $type = \PDO::PARAM_NULL;
        elseif(is_string($value))
            $type = \PDO::PARAM_STR;
        else
            $type = FALSE;

		return $type;
	}
	
	public function execute(Query $query=null)
	{
		$query->startTimer();
		
		$bindParam = 1;
		foreach($query->params() as $param) {
			$this->bindValue($bindParam++,$param);
		}
		
		$success = $this->_stmt->execute();
		
		$query->setSuccess($success);
		
		$query->stopTimer();
		
		if(!$query->wasSuccessful()) {
			$query->setError($this->_stmt->errorInfo());
			$this->_dexter->saveQuery($query);
			throw new \Exception($query->errorMessage());
		}else{
			$query->setResults($this->_stmt->fetchAll(\PDO::FETCH_ASSOC));
			$query->setRowCount($this->_stmt->rowCount());
			$this->_dexter->saveQuery($query);
		}
		
		return $query->wasSuccessful();
	}
	
	public function sql()
	{
		return $this->_sql;
	}
}