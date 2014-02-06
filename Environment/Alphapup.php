<?php
/**
 * This file actually loads the framework by extending 
 * the framework kernel and configuring various elements 
 * within the kernel.
 */
use Alphapup\Core\Kernel\Kernel;

class Alphapup extends Kernel
{	
	public function buildPlugins()
	{
		$plugins = array(
			'Alphapup' => new Alphapup\Plugin\Plugin(),
			'LoremIpsum' => new LoremIpsum\Plugin\Plugin(),
		);
		
		if($this->getEnvironment() == 'dev') {
			//$plugins['PluginName'] = new Another\Plugin\Plugin();
		}
		
		return $plugins;
	}
	
	public function loadEnvironmentConfig()
	{
		return $this->importConfigFile(__DIR__.'/Config/'.$this->_environment.'.php');
	}
}