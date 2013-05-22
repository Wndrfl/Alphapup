<?php
namespace Alphapup\Component\Carto\Proxy;

use Alphapup\Component\Carto\Carto;
use Alphapup\Component\Carto\Mapping;

class ProxyFactory
{
	private
		$_carto,
		$_proxyDir,
		$_proxyNamespace;
		
	public function __construct(Carto $carto,$proxyDir,$proxyNamespace)
	{
		$this->_carto = $carto;
		$this->_proxyDir = $proxyDir;
		$this->_proxyNamespace = $proxyNamespace;
	}
	
	private function _generateSleep(Mapping $mapping)
    {
        $sleepImpl = '';

        if($mapping->hasMethod('__sleep')) {
            $sleepImpl .= 'return array_merge(array(\'__isInitialized__\'), parent::__sleep());';
        }else{
            $sleepImpl .= 'return array_merge(array(\'__isInitialized__\'), array_keys((array)$this));';
        }

        return $sleepImpl;
    }
		
	public function createProxy($className)
	{
		$mapping = $this->_carto->mapping($className);
		
		$content = self::$_proxyClassTemplate;
		
		// generate methods
		$methods = '';
		$writtenMethods = array();
		foreach($mapping->methods() as $method) {
			
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
		
		$sleepImpl = $this->_generateSleep($mapping);
		$cloneImpl = $mapping->hasMethod('__clone') ? 'parent::__clone();' : ''; // hasMethod() checks case-insensitive;
		
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
			$this->proxyName($className),
			$mapping->className(),
			$methods,
			$sleepImpl,
			$cloneImpl
		);
		
		$content = str_replace($templates,$replacers,$content);
		file_put_contents($this->proxyFileName($className),$content,LOCK_EX);
	}
	
	public function proxy($className,$id)
	{
		$proxyName = $this->proxyName($className);
		$class = $this->_proxyNamespace.'\\'.$proxyName;
		
		if(!class_exists($class)) {
			$this->createProxy($className);
			
			require_once $this->proxyFileName($className);
		}
		
		$proxy = new $class($this->_carto->library()->librarian($className),$id);
		return $proxy;
	}
	
	public function proxyFileName($className)
	{
		return rtrim($this->_proxyDir,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$this->proxyName($className).'.php';
	}
	
	public function proxyName($className)
	{
		$mapping = $this->_carto->mapping($className);
		return $mapping->entityName().'Proxy';
	}
	
	/** Proxy class code template */
    private static $_proxyClassTemplate =
'<?php
namespace <namespace>;

class <proxyClassName> extends \<className> implements \Alphapup\Component\Carto\Proxy\Proxy
{
    private
		$_librarian,
		$_identifier;
		
    public $__isInitialized__ = false;

    public function __construct($librarian, $identifier)
    {
        $this->_librarian = $librarian;
        $this->_identifier = $identifier;
    }

    /** @private */
    public function __load()
    {
        if(!$this->__isInitialized__ && $this->_librarian) {
            $this->__isInitialized__ = true;

            if (method_exists($this, "__wakeup")) {
                // call this after __isInitialized__ to avoid infinite recursion
                // but before loading.
                $this->__wakeup();
            }

			$options = array(\'useEntity\'=>$this);
            if(!$this->_librarian->fetchOne($this->_identifier,$options)) {
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