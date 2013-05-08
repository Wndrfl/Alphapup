<?php
namespace Alphapup\Core\DependencyInjection;

use Alphapup\Core\Config\ConfigHandler;
use Alphapup\Core\Config\ConfigImporter;
use Alphapup\Core\DependencyInjection\Exception\CircularReferenceException;
use Alphapup\Core\DependencyInjection\Exception\InvalidClassDescriptionException;
use Alphapup\Core\DependencyInjection\Exception\ServiceNotFoundException;

class Container
{
	private
		$_config,
		$_definitions = array(),
		$_loading = array(),
		$_methods = array(),
		$_services = array(),
		$_tags = array();
	
	public function __construct(ConfigHandler $config)
	{
		$this->setConfigHandler($config);
		$this->set('alphapup.container',$this);
		$this->set('alphapup.container.config',$this->_config);
	}
	
	private function _getReference($variable,$parent)
	{
		if(strpos($variable,'@') === 0) {
			try {
				$reference = substr($variable,1);
				$variable = $this->get($reference,$parent);
			}catch(CircularReferenceException $e) {
				trigger_error('A circular dependency was detected between '.$parent.' and '.$reference.'',E_USER_ERROR);
			}catch(ServiceNotFoundException $e) {
				trigger_error($parent.' requested the service "'.$reference.'" but we couldn\'t find it',E_USER_ERROR);
			}
		}elseif(strpos($variable,'#') === 0) {
			try {
				$tag = substr($variable,1);
				$tagged = $this->getTagged($tag);
				return $tagged;
			}catch(CircularReferenceException $e) {
				trigger_error('A circular dependency was detected between '.$parent.' and a service tagged with '.$tag.'',E_USER_ERROR);
			}
		}
		return $variable;
	}
	
	/**
	 * Loops through the config, finds service definitions,
	 * and registers them.
	 * 
	 * Then, it loops through the definitions and generates 
	 * a getter for each definition that will be used when
	 * the framework calls for a service later.
	 **/
	public function compile()
	{
		$this->_config->compile();
		
		if($services = $this->_config->get('services')) {
			$services = $services->toArray();
			foreach($services as $id => $service) {
				try{
					$this->registerService($id,$service);
				}catch(InvalidClassDescriptionException $e) {
					trigger_error($e->getMessage(),E_USER_WARNING);
				}
			}
		}
		foreach($this->_definitions as $definition) {
			$methodName = $this->getNameForMethod($definition->id());
			$container = $this;
			$this->_methods[$methodName] = function() use ($container,$definition) {
				$class = $definition->params('class');
				
				$arguments = array();
				foreach($definition->params('arguments',array()) as $k => $v) {
					$arg = $container->getReferencesRecursive($v,$definition->id());
					$arguments[strtolower($k)] = $arg;
				}
				
				$reflection = new \ReflectionClass($class);
				$service = call_user_func_array(array(
					 $reflection,'newInstance'), 
					$arguments);
					
				if($definition->shared()) {
					$container->set($definition->id(),$service);
				}
				
				return $service;
			};
		}
	}
	
	/**
	 * Gets a service from the container.
	 * 
	 * First, attempts to find a cached version of the service. If
	 * not found, it uses the custom generated getter to load the 
	 * service.
	 **/
	public function get($id)
	{
		if(isset($this->_services[$id])) {
			return $this->_services[$id];
		}
		
		if(isset($this->_loading[$id])) {
			throw new CircularReferenceException($id);
		}
		
		if(isset($this->_methods[$this->getNameForMethod($id)])) {
			$this->_loading[$id] = true;
			$service = $this->_methods[$this->getNameForMethod($id)]();
			unset($this->_loading[$id]);
			return $service;
		}
		
		throw new ServiceNotFoundException($id);
	}
	
	public function getConfig($id=null)
	{
		return (!is_null($id)) ? $this->_config->get($id) : $this->_config;
	}
	
	public function getNameForMethod($id) {
		return 'get'.strtr($id, array('_' => '', '.' => '_')).'Service';
	}
	
	public function getReferencesRecursive($variable,$parent)
	{
		if(is_array($variable)) {
			foreach($variable as $k => $v) {
				$variable[strtolower($k)] = $this->getReferencesRecursive($v,$parent);
			}
		}else{
			$variable = $this->_getReference($variable,$parent);
		}
		return $variable;
	}
	
	public function getTagged($tag)
	{
		if(isset($this->_tags[$tag])) {
			$services = array();
			foreach($this->_tags[$tag] as $id) {
				$services[] = $this->get($id);
			}
			return $services;
		}
		return array();
	}
	
	public function has($id)
	{
		return (isset($this->_services[$id])) ? true : false;
	}
	
	public function importConfigFile($path)
	{
		$importer = new ConfigImporter();
		try {
			$importer->import($this->_config,$path);
		}catch(\Exception $e) {
			trigger_error($e->getMessage(),E_USER_ERROR);
		}
	}
	
	/**
	 * Registers service in the container for use by the
	 * framework. Saves services a "definitions".
	 * 
	 * Can use 'tags' as a way to categorize a service.
	 **/
	public function registerService($id,$service)
	{
		if(!isset($service['class'])) {
			throw new InvalidClassDescriptionException($id);
		}
		$id = strtolower($id);
		if(isset($service['tags']) && is_array($service['tags'])) {
			foreach($service['tags'] as $tag) {
				if(!isset($this->_tags[$tag])) {
					$this->_tags[$tag] = array();
				}
				$this->_tags[$tag][] = $id;
			}
		}
		$this->_definitions[$id] = new Definition($id,$service);
	}
	
	/**
	 * Saves and instance of a service in the container as 
	 * a cached copy for later use.
	 **/
	public function set($service,$instance)
	{
		$this->_services[$service] = $instance;
	}
	
	public function setConfigHandler(ConfigHandler $config)
	{
		$this->_config = $config;
	}
}