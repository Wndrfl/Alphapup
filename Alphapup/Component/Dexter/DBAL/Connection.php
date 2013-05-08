<?php
namespace Alphapup\Component\Dexter\DBAL;

use Alphapup\Component\Dexter\DBAL\Statement;
use Alphapup\Component\Dexter\Dexter;
use Alphapup\Component\Dexter\Query;

class Connection
{
	private 
		$_connection,
		$_database,
		$_host,
		$_password,
		$_schemas = array(),
		$_username;
	
	public function __construct($host,$username,$password,$database)
	{
		$this->setHost($host);
		$this->setUsername($username);
		$this->setPassword($password);
		$this->setDatabase($database);
	}
	
	public function __sleep()
	{
		return array(
			'_database',
			'_host',
			'_password',
			'_schemas',
			'_username'
		);
	}
	
	public function __unset($v)
	{
		$this->close();
		unset($this);
	}
	
	public function close($db=null)
	{
		$this->_connection = null;
	}
	
	public function connection()
	{
		if(!is_null($this->_connection)) {
			return $this->_connection;
		}
		$this->_connection = new \PDO(
			'mysql:host='.$this->_host.';dbname='.$this->_database,
			$this->_username,
			$this->_password);
		if(!$this->_connection) {
			$this->_error($this->_errors['badCredentials']);
			return false;
		}
		return $this->_connection;
	}
	
	public function lastInsertId()
	{
		return $this->connection()->lastInsertId();
	}
	
	public function statement(Dexter $dexter,$sql)
	{
		$connection = $this->connection();
		$stmt = $connection->prepare($sql);
		
		return new Statement($dexter,$sql,$stmt);
	}

	
	public function setDatabase($database)
	{
		$this->_database = $database;
	}
	
	public function setHost($host)
	{
		$this->_host = $host;
	}
	
	public function setPassword($password)
	{
		$this->_password = $password;
	}
	
	public function setUsername($username)
	{
		$this->_username = $username;
	}
	
	
}