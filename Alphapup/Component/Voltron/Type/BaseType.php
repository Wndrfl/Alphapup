<?php
namespace Alphapup\Component\Voltron\Type;

use Alphapup\Component\Voltron\Voltron;
use Alphapup\Component\Voltron\VoltronBuilder;
use Alphapup\Component\Voltron\VoltronPluginInterface;
use Alphapup\Component\Voltron\VoltronTypeInterface;
use Alphapup\Component\Voltron\VoltronView;

abstract class BaseType implements VoltronTypeInterface
{
	private
		$_plugins = array();
		
	public function configureVoltron(VoltronBuilder $voltronBuilder,array $options=array())
	{
		return false;
	}
		
	public function defaultOptions()
	{
		return array();
	}
	
	public function parent()
	{
		return false;
	}
	
	public function plugins()
	{
		return $this->_plugins;
	}

	public function setPlugin(VoltronPluginInterface $plugin)
	{
		$this->_plugins[$plugin->name()] = $plugin;
	}
	
	public function setupView(Voltron $voltron,VoltronView $view)
	{
		
	}
}