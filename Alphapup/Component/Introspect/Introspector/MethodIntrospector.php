<?php
namespace Alphapup\Component\Introspect\Introspector;

use Alphapup\Component\Introspect\Introspector\AnnotatedIntrospector;
use Alphapup\Component\Introspect\Introspector\ParameterIntrospector;

class MethodIntrospector extends AnnotatedIntrospector
{
	private
		$_parameters;
	
	public function __construct(\ReflectionMethod $reflection)
	{
		parent::__construct($reflection);
	}
	
	public function isConstructor()
	{
		return $this->_reflector->isConstructor();
	}
	
	public function isFinal()
	{
		return $this->_reflector->isFinal();
	}

	public function isPrivate()
	{
		return $this->_reflector->isPrivate();
	}
	
	public function isPublic()
	{
		return $this->_reflector->isPublic();
	}
	
	public function isStatic()
	{
		return $this->_reflector->isStatic();
	}
	
	public function name()
	{
		return $this->_reflector->getName();
	}
	
	public function parameters()
	{
		if(!empty($this->_parameters)) {
			return $this->_parameters;
		}
		$params = array();
		foreach($this->_reflector->getParameters() as $parameter) {
			$params[$parameter->getName()] = new ParameterIntrospector($parameter);
		}
		$this->_parameters = $params;
		return $this->_parameters;
	}
	
	public function returnsReference()
	{
		return $this->_reflector->returnsReference();
	}
}