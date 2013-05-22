<?php
namespace Alphapup\Component\Carto;

use Alphapup\Component\Dexter\Dexter;
use Alphapup\Component\Carto\CQL\CQLBuilder;
use Alphapup\Component\Carto\CQL\CQLQuery;
use Alphapup\Component\Carto\Mapping;
use Alphapup\Component\Carto\Repository;
use Alphapup\Component\Carto\Library;
use Alphapup\Component\Carto\Proxy\ProxyFactory;
use Alphapup\Component\Introspect\Introspect;
use Alphapup\Core\Config\ConfigHandler;

class Carto
{
	private
		$_configHandler,
		$_dexter,
		$_entityAliases = array(),
		$_introspect,
		$_library,
		$_mappings = array(),
		$_repositories = array(),
		$_proxyFactory;
		
	public function __construct(Dexter $dexter,Introspect $introspect,$entityAliases,$proxyDir,$proxyNamespace)
	{
		$this->setEntityAliases($entityAliases);
		
		$this->_dexter = $dexter;
		$this->_introspect = $introspect;
		
		$this->_library = new Library($this);
		$this->_proxyFactory = new ProxyFactory($this,$proxyDir,$proxyNamespace);
	}
	
	public function className($alias)
	{
		$check = strtolower($alias);
		return (isset($this->_entityAliases[$check])) ? $this->_entityAliases[$check] : $alias;
	}
	
	public function commit($className=null)
	{
		$this->_library->commit($className);
	}
	
	public function cql($cql,array $params=array())
	{
		return new CQLQuery($this,$cql,$params);
	}
	
	public function cqlBuilder()
	{
		return new CQLBuilder($this);
	}
	
	public function dexter()
	{
		return $this->_dexter;
	}
	
	public function library()
	{
		return $this->_library;
	}
	
	public function mapping($className)
	{
		$className = $this->className($className);
		$class = $this->_introspect->inspectClass($className);
		
		if(!isset($this->_mappings[$class->name()])) {
			$this->_mappings[$class->name()] = new Mapping($class);
		}
		return $this->_mappings[$class->name()];
	}
	
	public function persist($entity)
	{
		$this->_library->persist($entity);
	}
	
	public function proxyFactory()
	{
		return $this->_proxyFactory;
	}
	
	public function remove($entity)
	{
		$this->_library->remove($entity);
	}
	
	public function repository($className)
	{
		$className = $this->className($className);
		if(isset($this->_repositories[$className])) {
			return $this->_repositories[$className];
		}
		$mapping = $this->mapping($className);
		$repository = new Repository($this,$mapping);
		$this->_repositories[$className] = $repository;
		return $this->_repositories[$className];
	}
	
	public function setEntityAlias($alias,$className)
	{
		$this->_entityAliases[$alias] = $className;
	}
	
	public function setEntityAliases($entityAliases=array())
	{
		foreach($entityAliases as $alias => $className) {
			$this->setEntityAlias($alias,$className);
		}
	}
}