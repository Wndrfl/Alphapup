<?php
namespace Alphapup\Component\Fetch\Proxy;

use Alphapup\Component\Fetch\Fetch;
use Alphapup\Component\Fetch\EntityMapper;

class OneToOneProxyFactory
{
	private
		$_fetch,
		$_proxyDir,
		$_proxyNamespace;
		
	public function __construct(Fetch $fetch,$proxyDir,$proxyNamespace)
	{
		$this->_fetch = $fetch;
		$this->_proxyDir = $proxyDir;
		$this->_proxyNamespace = $proxyNamespace;
	}
	
	private function _generateSleep(EntityMapper $entityMapper)
    {
        $sleepImpl = '';

        if($entityMapper->hasMethod('__sleep')) {
            $sleepImpl .= 'return array_merge(array(\'__isInitialized__\'), parent::__sleep());';
        }else{
            $sleepImpl .= 'return array_merge(array(\'__isInitialized__\'), array_keys((array)$this));';
        }

        return $sleepImpl;
    }
		
	public function createProxy($entityAlias,EntityMapper $entityMapper)
	{	
		$content = self::$_proxyClassTemplate;
		
		// generate methods
		$methods = '';
		$writtenMethods = array();
		foreach($entityMapper->methods() as $method) {
			
			if($method->isConstructor() || in_array($method->name(),array('__sleep','__clone'))) {
				continue;
			}
			
			if(isset($writtenMethods[$method->name()])) {
				continue;
			}
			$writtenMethods[$method->name()] = true;
			
			// we only need to copy methods that are public accessible
			if($method->isPublic() || $method->isFinal() || $method->isStatic()) {
				$methods .= "\n"."\t".'public function ';
				if($method->returnsReference()) {
					$methods .= '&';
				}
				$methods .= $method->name().'(';
				
				$arguments = '';
				$parameters = '';
				$firstParam = true;
				
				$params = $method->parameters();
				foreach($params as $parameter) {
					if($firstParam) {
						$firstParam = false;
					}else{
						$arguments .= ', ';
						$parameters .= ', ';
					}
					
					// typehint
					if($typehint = $parameter->typehint()) {
						$parameters .= '\\'.$typehint->name().' ';
					}elseif($parameter->isArray()) {
						$parameters .= 'array ';
					}
					
					if($parameter->isPassedByReference()) {
						$parameters .= '&';
					}
					
					$arguments .= '$'.$parameter->name();
					$parameters .= '$'.$parameter->name();
					
					if($parameter->isDefaultValueAvailable()) {
						$parameters .= ' = '.var_export($parameter->defaultValue(),true);
					}
				}
				
				$methods .= $parameters.')';
				$methods .= "\n"."\t".'{';
				
				$methods .= "\n\t\t".'$this->__load();';
				$methods .= "\n\t\t".'return parent::'.$method->name().'('.$arguments.');';
				
				$methods .= "\n"."\t".'}'."\n";
			}
		}
		
		$sleepImpl = $this->_generateSleep($entityMapper);
		$cloneImpl = $entityMapper->hasMethod('__clone') ? 'parent::__clone();' : ''; // hasMethod() checks case-insensitive;
		
		$templates = array(
			'<namespace>',
			'<proxyClassName>',
			'<className>',
			'<methods>',
			'<sleepImpl>',
			'<cloneImpl>'
		);
		$replacers = array(
			$this->_proxyNamespace,
			$this->proxyName($entityAlias),
			$entityMapper->entityFullName(),
			$methods,
			$sleepImpl,
			$cloneImpl
		);
		
		$content = str_replace($templates,$replacers,$content);
		file_put_contents($this->proxyFileName($entityAlias),$content,LOCK_EX);
	}
	
	public function proxy($entityAlias,$id)
	{
		$entityMapper = $this->_fetch->entityMapper($entityAlias);
		
		$proxyName = $this->proxyName($entityAlias);
		$class = $this->_proxyNamespace.'\\'.$proxyName;
		
		if(!class_exists($class)) {
			$this->createProxy($entityAlias,$entityMapper);
			require_once $this->proxyFileName($entityAlias);
		}
		
		$proxy = new $class($this->_fetch->entityLibrarian($entityAlias),$id);
		return $proxy;
	}
	
	public function proxyFileName($entityAlias)
	{
		return rtrim($this->_proxyDir,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$this->proxyName($entityAlias).'.php';
	}
	
	public function proxyName($entityAlias)
	{
		return $entityAlias.'Proxy';
	}
	
	/** Proxy class code template */
    private static $_proxyClassTemplate =
'<?php
namespace <namespace>;

class <proxyClassName> extends \<className> implements \Alphapup\Component\Fetch\Proxy\Proxy
{
    private
		$_entityLibrarian,
		$_identifier;
		
    public $__isInitialized__ = false;

    public function __construct($entityLibrarian, $identifier)
    {
        $this->_entityLibrarian = $entityLibrarian;
        $this->_identifier = $identifier;
    }

    /** @private */
    public function __load()
    {
        if(!$this->__isInitialized__ && $this->_entityLibrarian) {
            $this->__isInitialized__ = true;

            if (method_exists($this, "__wakeup")) {
                // call this after __isInitialized__ to avoid infinite recursion
                // but before loading.
                $this->__wakeup();
            }

			$options = array(\'useEntity\' => $this);
            if(!$this->_entityLibrarian->fetchOne($this->_identifier,$options)) {
                // DO EXCEPTION
				return false;
            }

            unset($this->_library,$this->_identifier);
        }
    }
    
    <methods>

    public function __sleep()
    {
        <sleepImpl>
    }

    public function __clone()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;
            $class = $this->_entityPersister->getClassMetadata();
            $original = $this->_entityPersister->load($this->_identifier);
            if ($original === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            foreach ($class->reflFields AS $field => $reflProperty) {
                $reflProperty->setValue($this, $reflProperty->getValue($original));
            }
            unset($this->_entityPersister, $this->_identifier);
        }
        <cloneImpl>
    }
}';
}