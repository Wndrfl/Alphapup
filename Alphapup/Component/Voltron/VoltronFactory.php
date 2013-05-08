<?php
namespace Alphapup\Component\Voltron;

use Alphapup\Component\Voltron\VoltronBuilder;
use Alphapup\Component\Voltron\VoltronPluginManager;
use Alphapup\Component\Voltron\VoltronTypeManager;

class VoltronFactory
{
	private
		$_pluginManager,
		$_types=array(),
		$_typeManager;
		
	public function __construct(VoltronTypeManager $typeManager,VoltronPluginManager $pluginManager)
	{
		$this->setTypeManager($typeManager);
		$this->setPluginManager($pluginManager);
	}
	
	public function build($name,$type=null,array $options=array())
	{
		$type = (!is_null($type)) ? $type : 'form';
		return $this->assembleVoltron($type,$name,$options);
	}
	
	public function assembleVoltron($type,$name,array $options=array())
	{
		// assemble type hierarchy and options
		$types = array();
		$allOptions = $options;
		while(!is_null($type)) {
			if(!$type = $this->type($type)) {
				break;
			}
			$typeOptions = $type->defaultOptions();
			$replacedOptions = array_replace($typeOptions,$options);
			$allOptions = array_merge($allOptions,$replacedOptions);
			
			foreach($type->plugins() as $plugin) {
				$replacedOptions = array_replace($plugin->defaultOptions(),$options);
				$allOptions = array_merge($allOptions,$replacedOptions);
			}
			
			array_unshift($types,$type);
			$type = $type->parent();
		}
		
		// allow types to configure new VoltronBuilder
		$voltronBuilder = new VoltronBuilder($name,$this);
		$voltronBuilder->setTypes($types);
		foreach($types as $type) {
			$type->configureVoltron($voltronBuilder,$allOptions);
			foreach($type->plugins() as $plugin) {
				$plugin->configureVoltron($voltronBuilder,$allOptions);
			}
		}
		return $voltronBuilder;
	}
	
	public function setTypeManager(VoltronTypeManager $typeManager)
	{
		$this->_typeManager = $typeManager;
	}
	
	public function setPluginManager(VoltronPluginManager $pluginManager)
	{
		$this->_pluginManager = $pluginManager;
	}
	
	public function type($name)
	{
		if(isset($this->_types[$name])) {
			return $this->_types[$name];
		}
		$type = $this->_typeManager->type($name);
		if(!$type) {
			return false;
		}
		
		$plugins = $this->_pluginManager->pluginsForType($type->name());
		foreach($plugins as $plugin) {
			$type->setPlugin($plugin);
		}
		$this->_types[$name] = $type;
		return $this->_types[$name];
	}
}