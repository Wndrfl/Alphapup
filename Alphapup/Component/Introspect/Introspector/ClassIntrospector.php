<?php
namespace Alphapup\Component\Introspect\Introspector;

use Alphapup\Component\Introspect\Introspector\AnnotatedIntrospector;
use Alphapup\Component\Introspect\Introspector\MethodIntrospector;
use Alphapup\Component\Introspect\Introspector\PropertyIntrospector;

class ClassIntrospector extends AnnotatedIntrospector
{
	private
		$_methods,
		$_properties;
		
	public function __construct(\ReflectionClass $reflection)
	{
		parent::__construct($reflection);
	}
	
	public function classNamespace()
	{
		return $this->_reflector->getNamespace();
	}
	
	public function hasInterface($interfaceName)
	{
		$interfaces = $this->interfaces();
		foreach($interfaces as $interface) {
			if($interface == $interfaceName) {
				return true;
			}
		}
		return false;
	}
	
	public function hasMethod($method)
	{
		return $this->_reflector->hasMethod($method);
	}
	
	public function interfaces()
	{
		return $this->_reflector->getInterfaces();
	}
	
	public function methods()
	{
		if(empty($this->_methods)) {
			$this->_methods = array();
			$methods = $this->_reflector->getMethods();
			foreach($methods as $method) {
				$this->_methods[$method->getName()] = new MethodIntrospector($method);
			}
		}
		
		return $this->_methods;
	}
	
	public function method($name)
	{
		// DO EXCEPTION
		return (isset($this->_methods[$name])) ? $this->_methods[$name] : false;
	}
	
	public function methodsWithAnnotation($name)
	{
		$methods = array();
		foreach($this->methods() as $method) {
			if($method->annotation($name)) {
				$methods[] = $method;
			}
		}
		return $methods;
	}
	
	public function methodsWithAnnotationPrefix($prefix)
	{
		$methods = array();
		foreach($this->methods() as $method) {
			if($method->hasAnnotationPrefix($prefix)) {
				$methods[] = $method;
			}
		}
		return $methods;
	}
	
	public function name()
	{
		return $this->_reflector->getName();
	}
	
	public function parentClass()
	{
		return $this->_reflector->getParentClass();
	}
	
	public function properties()
	{
		if(empty($this->_properties)) {
			$this->_properties = array();
			$properties = $this->_reflector->getProperties();
			foreach($properties as $property) {
				$this->_properties[$property->getName()] = new PropertyIntrospector($property);
			}
		}
		
		return $this->_properties;
	}
	
	public function propertiesWithAnnotation($name)
	{
		$properties = array();
		foreach($this->properties() as $property) {
			if($property->annotation($name)) {
				$properties[] = $property;
			}
		}
		return $properties;
	}
	
	public function propertiesWithAnnotationPrefix($prefix)
	{
		$properties = array();
		foreach($this->properties() as $property) {
			if($property->hasAnnotationPrefix($prefix)) {
				$properties[] = $property;
			}
		}
		return $properties;
	}
	
	public function property($name)
	{
		// DO EXCEPTION
		return (isset($this->_properties[$name])) ? $this->_properties[$name] : false;
	}
	
	public function shortName()
	{
		return $this->_reflector->getShortName();
	}
	
	public function value($instance=null,$property)
	{
		$property = $this->property($property);
		return $property->value($instance);
	}
}