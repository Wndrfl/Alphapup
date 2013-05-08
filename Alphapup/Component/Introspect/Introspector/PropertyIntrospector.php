<?php
namespace Alphapup\Component\Introspect\Introspector;

use Alphapup\Component\Introspect\Introspector\AnnotatedIntrospector;

class PropertyIntrospector extends AnnotatedIntrospector
{
		
	public function __construct(\ReflectionProperty $reflection)
	{
		parent::__construct($reflection);
	}
	
	public function name()
	{
		return $this->_reflector->getName();
	}
	
	public function isPublic()
	{
		return $this->_reflector->isPublic();
	}
	
	public function setAccessible()
	{
		$this->_reflector->setAccessible(true);
	}
	
	public function setInaccessible()
	{
		$this->_reflector->setAccessible(false);
	}
	
	public function setValue($instance=null,$value)
	{
		if(!$this->isPublic()) {
			$this->setAccessible();
		}
		$this->_reflector->setValue($instance,$value);
	}
	
	/**
	 * @return string Value of instance's property, or default value if NULL
	 */
	public function value($instance=null)
	{
		if(!$this->isPublic()) {
			$this->setAccessible();
		}
		return $this->_reflector->getValue($instance);
	}
}