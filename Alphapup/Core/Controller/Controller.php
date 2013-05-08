<?php
namespace Alphapup\Core\Controller;

use Alphapup\Core\DependencyInjection\Container;
use Alphapup\Core\DependencyInjection\ContainerAwareInterface;

class Controller implements ContainerAwareInterface
{
	protected
		$_container;
		
	public function __construct() {}
	
	public function disableProfiler()
	{
		$this->get('profiler')->disable();
	}
	
	public function enableProfiler()
	{
		$this->get('profiler')->enable();
	}
	
	public function get($id)
	{
		return $this->_container->get($id);
	}
	
	public function getConfig($id=null)
	{
		return $this->_container->getConfig($id);
	}
	
	public function has($id)
	{
		return $this->_container->has($id);
	}
	
	public function setContainer(Container $container)
	{
		$this->_container = $container;
	}
}