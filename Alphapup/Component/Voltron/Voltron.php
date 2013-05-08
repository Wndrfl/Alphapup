<?php
namespace Alphapup\Component\Voltron;

use Alphapup\Component\Voltron\VoltronTypeInterface;
use Alphapup\Component\Voltron\VoltronView;

class Voltron implements \ArrayAccess
{
	private
		$_attributes=array(),
		$_bound=false,
		$_children=array(),
		$_errors=array(),
		$_name,
		$_parent,
		$_types=array(),
		$_value,
		$_validators=array();
		
	public function __construct($name,array $types=array(),array $attributes=array(),array $validators=array())
	{
		$this->setAttributes($attributes);
		$this->setName($name);
		$this->setTypes($types);
		$this->setValidators($validators);
	}
	
	public function add(Voltron $voltron)
	{
		$voltron->setParent($this);
		$this->_children[$voltron->name()] = $voltron;
	}
	
	public function attribute($name)
	{
		return (isset($this->_attributes[$name])) ? $this->_attributes[$name] : false;
	}
	
	public function attributes()
	{
		return $this->_attributes;
	}
	
	public function bind($data)
	{
		if(is_scalar($data) || is_null($data)) {
			$data = (string) $data;
		}
		
		if(count($this->_children) > 0) {
			if(!is_array($data)) {
				// DO EXCEPTION
				return;
			}
			
			foreach($this->_children as $name => $child) {
				if(!isset($data[$name])) {
					$data[$name] = null;
				}
			}
			
			foreach($data as $name => $value) {
				if(isset($this->_children[$name])) {
					$this->_children[$name]->bind($value);
				}
			}
			
		}else{
			$this->setValue($data);
		}
		
		$this->validate();
		
		$this->_bound = true;
	}
	
	public function bindRequest()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		switch($method) {
			case 'POST':
				$data = array_replace_recursive($_POST,$_FILES);
				$data = (isset($data[$this->name()])) ? $data[$this->name()] : array();
				break;
			case 'GET':
				$data = $_GET;
				$data = (isset($data[$this->name()])) ? $data[$this->name()] : array();
				break;
			default:
				$data = array();
				break;
		}
		$this->bind($data);
	}
	
	public function child($name)
	{
		return (isset($this->_children[$name])) ? $this->_children[$name] : false;
	}
	
	public function children()
	{
		return $this->_children;
	}
	
	public function errors()
	{
		return $this->_errors;
	}
	
	public function field($name)
	{
		if(!isset($this->_children[$name])) {
			// DO EXCEPTION
			return false;
		}
		return $this->_children[$name];
	}
	
	public function firstError()
	{
		return (isset($this->_errors[0])) ? $this->_errors[0] : false;
	}
	
	public function isValid()
	{
		if(!$this->_bound) {
			// DO EXCEPTION
			return false;
		}
		
		return (count($this->_errors) == 0) ? true : false;
	}
	
	public function name()
	{
		return $this->_name;
	}
	
	public function offsetExists($offset)
	{
		return (isset($this->_children[$offset])) ? true : false;
	}

	public function offsetGet($offset)
	{
		return (isset($this->_children[$offset])) ? $this->_children[$offset]->value() : false;
	}
	
	public function offsetSet($offset,$value)
	{
		if(!$value instanceof self) {
			return false;
		}
		return $this->_children[$offset] = $value;
	}
	
	public function offsetUnset($offset)
	{
		unset($this->_children[$offset]);
	}
	
	public function parent()
	{
		return $this->_parent;
	}
	
	public function setAttribute($key,$val)
	{
		$this->_attributes[$key] = $val;
	}
	
	public function setAttributes($attributes=array())
	{
		foreach($attributes as $key => $val) {
			$this->setAttribute($key,$val);
		}
	}
	
	public function setError($error)
	{
		$this->_errors[] = $error;
		if(!empty($this->_parent)) {
			$this->_parent->setError($error);
		}
	}
	
	public function setName($name)
	{
		$this->_name = $name;
	}
	
	public function setParent(Voltron $voltron)
	{
		$this->_parent = $voltron;
	}

	public function setType(VoltronTypeInterface $type)
	{
		$this->_types[] = $type;
	}
	
	public function setTypes($types=array())
	{
		foreach($types as $type) {
			$this->setType($type);
		}
	}
	
	public function setValidator(VoltronValidatorInterface $validator)
	{
		$this->_validators[] = $validator;
	}
	
	public function setValidators($validators=array())
	{
		foreach($validators as $validator) {
			$this->setValidator($validator);
		}
	}
	
	public function setValue($value)
	{
		$this->_value = $value;
	}
	
	public function types()
	{
		return $this->_types;
	}
	
	public function validate()
	{		
		foreach($this->_validators as $validator) {
			$validator->validate($this);
		}
	}
	
	public function value()
	{
		if(count($this->_children) == 0) {
			return $this->_value;
		}else{
			$value = array();
			foreach($this->_children as $name => $child) {
				$value[$name] = $child->value();
			}
			return $value;
		}
	}
	
	public function view(VoltronView $parentView=null)
	{
		if(is_null($parentView) && !empty($this->_parent)) {
			$parentView = $this->_parent->view();
		}
		
		$view = new VoltronView();
		
		$view->setParent($parentView);
		
		foreach($this->_types as $type) {
			$type->setupView($this,$view);
			foreach($type->plugins() as $plugin) {
				$plugin->setupView($this,$view);
			}
		}
		
		$view->setTypes($this->_types);
		
		foreach($this->_children as $key => $child) {
			$view->setChild($key,$child->view($view));
		}
		
		return $view;
	}
}