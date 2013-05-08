<?php
namespace Alphapup\Component\Voltron;

use Alphapup\Component\Voltron\VoltronPluginInterface;

class VoltronPluginManager
{
	private
		$_plugins=array();
		
	public function __construct(array $plugins=array())
	{
		$this->setPlugins($plugins);
	}
	
	public function plugins()
	{
		return $this->_plugins;
	}
	
	public function pluginsForType($name)
	{
		return (isset($this->_plugins[$name])) ? $this->_plugins[$name] : array();
	}
	
	public function setPlugin(VoltronPluginInterface $plugin)
	{
		$this->_plugins[$plugin->pluginForType()][$plugin->name()] = $plugin;
	}
	
	public function setPlugins(array $plugins=array())
	{
		foreach($plugins as $plugin) {
			$this->setPlugin($plugin);
		}
	}
}