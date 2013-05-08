<?php
namespace Alphapup\Component\Introspect\Introspector;

use Alphapup\Component\Introspect\Introspector\BaseIntrospector;
use Alphapup\Component\Introspect\Introspector\ClassIntrospector;

class ParameterIntrospector extends BaseIntrospector
{	
	public function __construct(\ReflectionParameter $reflection)
	{
		parent::__construct($reflection);
	}
	
	public function defaultValue()
	{
		return $this->_reflector->getDefaultValue();
	}
	
	public function isArray()
	{
		return $this->_reflector->isArray();
	}
	
	public function isDefaultValueAvailable()
	{
		return $this->_reflector->isDefaultValueAvailable();
	}
	
	public function isOptional()
	{
		return $this->_reflector->isOptional();
	}
	
	public function isPassedByReference()
	{
		return $this->_reflector->isPassedByReference();
	}
	
	public function name()
	{
		return $this->_reflector->getName();
	}
	
	public function typehint()
	{
		if($reflector = $this->_reflector->getClass()) {
			return new ClassIntrospector($reflector);
		}
		return false;
	}
}