<?php
namespace Alphapup\Component\Fetch;

use Alphapup\Component\Dexter\Dexter;
use Alphapup\Component\Fetch\EntityLibrarian;
use Alphapup\Component\Fetch\EntityMapper;
use Alphapup\Component\Fetch\Proxy\OneToOneProxyFactory;
use Alphapup\Component\Fetch\PublicLibrary;
use Alphapup\Component\Introspect\Introspect;

class Fetch
{
	private
		$_entityAliases,
		$_entityLibrarians = array(),
		$_introspect,
		$_proxyFactory,
		$_publicLibrary;
		
	public function __construct(array $entityAliases=array(),Introspect $introspect,Dexter $dexter,$proxyDir,$proxyNamespace)
	{
		$this->_entityAliases = $entityAliases;
		$this->_introspect = $introspect;
		$this->_dexter = $dexter;
		$this->_publicLibrary = new PublicLibrary();
		$this->_oneToOneProxyFactory = new OneToOneProxyFactory($this,$proxyDir,$proxyNamespace);
	}
	
	function className($entityAlias)
	{
		$entityAlias = strtolower($entityAlias);
		return (isset($this->_entityAliases[$entityAlias]))
			? $this->_entityAliases[$entityAlias] : null;
	}
	
	function entityAlias($className)
	{
		foreach($this->_entityAliases as $k => $v) {
			if($v == $className)
				return $k;
		}
		return null;
	}
	
	function entityLibrarian($entityAlias)
	{
		if(isset($this->_entityLibrarians[$entityAlias]))
			return $this->_entityLibrarians[$entityAlias];
			
		$className = $this->className($entityAlias);
		if(!$className)
			throw new \Exception();
			
		// Create the EntityLibrarian
		$entityMapper = $this->entityMapper($entityAlias);
		$entityLibrarian = new EntityLibrarian($this,$entityMapper,$this->_dexter,$this->_publicLibrary);
		
		$this->_entityLibrarians[$entityAlias] = $entityLibrarian;
		return $this->_entityLibrarians[$entityAlias];
	}
	
	public function entityMapper($entityAlias)
	{
		if(isset($this->_entityMappers[$entityAlias]))
			return $this->_entityMappers[$entityAlias];
			
		$className = $this->className($entityAlias);
		$classIntrospector = $this->_introspect->inspectClass($className);
		$this->_entityMappers[$entityAlias] = new EntityMapper($classIntrospector);
		return $this->_entityMappers[$entityAlias];
	}
	
	public function fetch($entityAlias,$args=array())
	{
		try {
			$entityLibrarian = $this->entityLibrarian($entityAlias);
			return $entityLibrarian->fetchBy();
			
		} catch(\Exception $e) {
			// Bad things.
			echo $e;
		}
	}
	
	public function oneToOneProxyFactory()
	{
		return $this->_oneToOneProxyFactory;
	}
}