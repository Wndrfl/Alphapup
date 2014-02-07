<?php
namespace Alphapup\Plugin;

use Alphapup\Core\DependencyInjection\Container;
use Alphapup\Core\Kernel\PluginInterface;

class Plugin implements PluginInterface
{
	
	public function boot(Container $container)
	{
		$container->importConfigFile(__DIR__.'/Components.php');
		$container->importConfigFile(__DIR__.'/Core.php');
		$container->importConfigFile(__DIR__.'/Profiler.php');
		
		$this->setupCarto($container);
	}
	
	public function dir()
	{
		return realpath(__DIR__.'/..');
	}
	
	public function postBoot(Container $container)
	{
		$this->setupCartoNamespaces($container);
		$this->setupProfiler($container);
	}
	
	public function setupCarto(Container $container)
	{	
		if(!$cartoConfig = $container->getConfig('carto')) {
			$cartoConfig = $container->getConfig()->add('carto');
		}
		
		$proxyNamespace = $cartoConfig->get('proxy_namespace');
		$proxyDir = $cartoConfig->get('proxy_dir');
		
		if(!isset($proxyNamespace) || !$proxyNamespace) {
			$proxyNamespace = 'Proxy';
			$cartoConfig->add('proxy_namespace',$proxyNamespace);
		}
		if(!isset($proxyDir) || !$proxyDir) {
			$proxyDir = $container->getConfig('kernel')->cache_dir.'Carto/Proxies';
			$cartoConfig->add('proxy_dir',$proxyDir);
		}
	}
	
	public function setupCartoNamespaces(Container $container)
	{
		$loader = $container->get('alphapup.class_loader');
		$loader->registerNamespaces(array(
			$container->getConfig('carto')->get('proxy_namespace') => $container->getConfig('carto')->get('proxy_dir')
		));
	}
	
	public function setupProfiler(Container $container)
	{
		$profiler = $container->get('profiler');
		$collectors = $container->getTagged('data_collector');
		if(!is_array($collectors)) {
			return;
		}
		foreach($collectors as $collector) {
			$profiler->add($collector);
		}
	}
}