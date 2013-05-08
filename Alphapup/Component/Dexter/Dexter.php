<?php
namespace Alphapup\Component\Dexter;

use Alphapup\Component\Dexter\DBAL\Connection;
use Alphapup\Component\Dexter\DBAL\Table\Table;
use Alphapup\Component\Dexter\Query;
use Alphapup\Core\Cache\Cache;
use Alphapup\Core\Event\EventCenter;

class Dexter
{		
	private
		$_builders = array(),
		$_cache,
		$_cachePrefix = 'dexter',
		$_connection,
		$_eventCenter,
		$_queries = array(),
		$_tables = array();
		
	public function __construct(
		Connection $connection,
		Cache $cache,
		EventCenter $eventCenter)
	{
		$this->setConnection($connection);
		$this->setCache($cache);
		$this->setEventCenter($eventCenter);
	}
	
	private function _cacheName($pdo)
	{
		return $this->_cachePrefix.'.'.$this->_cache->hash($pdo->id());	
	}
	
	public function beginTransaction()
	{
		$query = $this->query('BEGIN');
		$this->execute($query);
	}
	
	public function commit()
	{
		$query = $this->query('COMMIT');
		$this->execute($query);
	}
	
	
	public function execute($query,$params=array(),$ttl=0)
	{
		if($query instanceof Statement) {
			$stmt = $query;
			$query = $this->query($stmt->sql(),$params);
		}else{
			if(!$query instanceof Query) {
				$query = $this->query($query,$params);
			}
			$stmt = $this->statement($query);
		}
		
		if($cached = $this->_cache->get($this->_cacheName($query))) {
			return $cached;
		}
		
		$stmt->execute($query);
		
		if($ttl != 0) {
			$name = $this->_cacheName($query);
			$this->_cache->set($name,$query,$ttl);
		}
		
		return $query;
	}
	
	public function lastInsertId()
	{
		return $this->_connection->lastInsertId();
	}
	
	public function queries()
	{
		return $this->_queries;
	}
	
	public function query($sql,$params=array())
	{
		$query = new Query($this,$sql,$params);
		return $query;
	}
	
	public function rollback()
	{
		$query = $this->query('ROLLBACK');
		$this->execute($query);
	}

	public function row($query,$params=array(),$ttl=0)
	{
		$pdo = $this->query($query,$params);
		$results = $pdo->results();
		return $results->firstRow();
	}
	
	public function rows($query,$params=array(),$ttl=0)
	{
		$pdo = $this->query($query,$params);
		return $pdo->results();
	}
	
	public function saveQuery(Query $query)
	{
		$this->_queries[] = $query;
	}
	
	public function setCache(Cache $cache)
	{
		$this->_cache = $cache;
	}
	
	public function setConnection(Connection $connection)
	{
		$this->_connection = $connection;
	}
	
	public function setEventCenter(EventCenter $eventCenter)
	{
		$this->_eventCenter = $eventCenter;
	}

	public function statement($query)
	{
		if($query instanceof Query) {
			$sql = $query->sql();
		}else{
			$sql = $query;
		}
		
		return $this->_connection->statement($this,$sql);
	}
	
	public function table($table)
	{
		if(!isset($this->_tables[$table])) {
			// DO CHANGE TTL TO 60
			$query = $this->query('SHOW columns FROM '.$table,array($table),0);
			$schema = $query->results()->toArray();
			$this->_tables[$table] = new Table($table,$schema);
		}
		return $this->_tables[$table];
	}
	
	public function transaction($queries=array())
	{
		$this->begin();
		
		try {
			foreach($queries as $query) {
				$this->execute($query);
			}
			$this->commit();
		}catch(\Exception $e) {
			$this->rollback();
			throw $e;
		}
	}
	
	public function totalRows($query,$params=array(),$ttl=0)
	{
		$pdo = $this->query($query,$params,$ttl);
		return ($pdo->wasSuccessful()) ? $pdo->rowCount() : 0;
	}
}