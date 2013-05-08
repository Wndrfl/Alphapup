<?php
namespace Alphapup\Component\Voltron;

use Alphapup\Component\Voltron\Voltron;
use Alphapup\Component\Voltron\VoltronFactory;
use Alphapup\Component\Voltron\VoltronTypeInterface;

class VoltronBuilder
{
	private
		$_attributes=array(),
		$_children = array(),
		$_factory,
		$_label,
		$_name,
		$_parent,
		$_types=array(),
		$_validators=array();
		
	public function __construct($name,VoltronFactory $factory)
	{
		$this->setName($name);
		$this->setFactory($factory);
	}
	
	public function add($child,$type=null,array $options=array())
	{
		if($child instanceof self) {
			$this->_children[$child->name()] = $child;
			return $this;
		}
		if(is_null($type)) {
			// DO EXCEPTION
			return $this;
		}
		if(is_string($child)) {
			$builder = $this->create($child,$type,$options);
			$builder->setParent($this);
			$this->_children[$child] = $builder;
		}
		return $this;
	}
	
	public function attributes()
	{
		return $this->_attributes;
	}
	
	public function create($name,$type,array $options=array()) {
		$voltron = $this->_factory->assembleVoltron($type,$name,$options);
		return $voltron;
	}
	
	public function form()
	{
		$voltron = new Voltron($this->name(),$this->types(),$this->attributes(),$this->validators());
		foreach($this->_children as $child) {
			$voltron->add($child->form());
		}
		return $voltron;
	}
	
	public function name()
	{
		return $this->_name;
	}
	
	public function setAttribute($key,$val)
	{
		$this->_attributes[$key] = $val;
		return $this;
	}
	
	public function setFactory(VoltronFactory $factory)
	{
		$this->_factory = $factory;
	}
	
	public function setName($name)
	{
		$this->_name = $name;
	}
	
	public function setParent(VoltronBuilder $voltronBuilder)
	{
		$this->_parent = $voltronBuilder;
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
	
	public function types()
	{
		return $this->_types;
	}
	
	public function validators()
	{
		return $this->_validators;
	}
}