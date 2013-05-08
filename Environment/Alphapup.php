<?php
use Alphapup\Core\Kernel\Kernel;

class Alphapup extends Kernel
{	
	public function buildPlugins()
	{
		return array(
			'Alphapup' => new Alphapup\Plugin\Plugin(),
			'LoremIpsum' => new LoremIpsum\Plugin\Plugin(),
		);
	}
	
	public function loadEnvironmentConfig()
	{
		return $this->importConfigFile(__DIR__.'/Config/'.$this->_environment.'.php');
	}
}