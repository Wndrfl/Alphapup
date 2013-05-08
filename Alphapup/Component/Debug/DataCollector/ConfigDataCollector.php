<?php
namespace Alphapup\Component\Debug\DataCollector;

use Alphapup\Component\Debug\DataCollector\BaseDataCollector;
use Alphapup\Core\DependencyInjection\Container;

class ConfigDataCollector extends BaseDataCollector
{
	private
		$_config,
		$_container;
		
	public function __construct(Container $container)
	{
		$this->setContainer($container);
	}
	
	public function collect()
	{
		$this->_config = $this->_container->getConfig();
		unset($this->_container); // for serialization
	}
	
	public function toArray()
	{
		return $this->_config->toArray();
	}
	
	public function name()
	{
		return 'config';
	}
	
	public function setContainer(Container $container)
	{
		$this->_container = $container;
	}
}