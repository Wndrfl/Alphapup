<?php
namespace Alphapup\Component\NitPick;

use Alphapup\Component\Introspect\Introspector\ClassIntrospector;
use Alphapup\Component\NitPick\NitPickTesterInterface;

class NitPickTester implements NitPickTesterInterface
{
	private
		$_annotationPrefix = 'NitPick',
		$_object,
		$_pendingTests = array(),
		$_tester;
		
	public function __construct($object=null,ClassIntrospector $class)
	{
		$this->setClass($class);
		$this->setObject($object);
	}
	
	private function _createMethodTest($method,$rule,array $options=array())
	{
		$params = (isset($options['arguments']) && is_array($options['arguments'])) ? $options['arguments'] : array();

		if(!is_null($this->_object) && is_string($method)) {
			$method = array($this->_object,$method);
		}
		if(!is_callable($method)) {
			// DO EXCEPTION
			return false;
		}
		
		ob_start();
		$value = call_user_func_array($method,$params);
		ob_end_clean();
		
		$test = $this->_createValueTest($value,$rule,$options);
		return $test;
	}
	
	private function _createValueTest($value,$rule,array $options=array())
	{
		$test = array(
			'value' => $value,
			'rule' => $rule,
			'options' => $options
		);
		return $test;
	}
	
	public function customRule($method,array $options=array())
	{
		$this->testMethod($method,'isTrue',$options);
		return $this;
	}
	
	public function runTests()
	{
		// first, check for a custom method for semantic testing
		if($this->_class->hasInterface('NitPickTestableInterface')) {
			$this->_object->testWithNitPick($this);
		}
		
		// check for property annotations
		$properties = $this->_class->propertiesWithAnnotationPrefix($this->_annotationPrefix);
		foreach($properties as $property) {
			$annots = $property->annotationsWithPrefix($this->_annotationPrefix);
			$value = $property->value($this->_object);
			foreach($annots as $rule => $options) {
				$options = (is_array($options)) ? $options : array();
				$this->testValue($value,lcfirst($rule),$options);
			}
		}
		
		// check for method annotations
		$methods = $this->_class->methodsWithAnnotationPrefix($this->_annotationPrefix);
		foreach($methods as $method) {
			$annots = $method->annotationsWithPrefix($this->_annotationPrefix);
			$m = array($this->_object,$method->name());
			foreach($annots as $rule => $options) {
				$options = (is_array($options)) ? $options : array();
				$this->testMethod($m,lcfirst($rule),$options);
			}
		}
	}
	
	public function setClass(ClassIntrospector $class)
	{
		$this->_class = $class;
	}
	
	public function setObject($object=null)
	{
		$this->_object = $object;
	}
	
	public function testMethod($method,$rule,array $options=array())
	{
		$this->_pendingTests[] = array('type'=>'method','method'=>$method,'rule'=>$rule,'options'=>$options);
		return $this;
	}
	
	public function tests()
	{
		$tests = array();
		foreach($this->_pendingTests as $test) {
			if($test['type'] == 'method') {
				$tests[] = $this->_createMethodTest($test['method'],$test['rule'],$test['options']);
			}else{
				$tests[] = $this->_createValueTest($test['value'],$test['rule'],$test['options']);
			}
		}
		return $tests;
	}
	
	public function testValue($value,$rule,array $options=array())
	{
		$this->_pendingTests[] = array('type'=>'value','value'=>$value,'rule'=>$rule,'options'=>$options);
		return $this;
	}
}