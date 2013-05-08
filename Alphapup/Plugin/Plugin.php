<?php
namespace Alphapup\Plugin;

use Alphapup\Core\DependencyInjection\Container;
use Alphapup\Core\Kernel\PluginInterface;

class Plugin implements PluginInterface
{
	
	public function boot(Container $container)
	{
		$container->importConfigFile(__DIR__.'/Assets.php');
		$container->importConfigFile(__DIR__.'/Components.php');
		$container->importConfigFile(__DIR__.'/Core.php');
		$container->importConfigFile(__DIR__.'/Profiler.php');
		$container->importConfigFile(__DIR__.'/Routes.php');
	}
	
	public function dir()
	{
		return realpath(__DIR__.'/..');
	}
	
	public function postBoot(Container $container)
	{		
		$this->setupAssets($container);
		$this->setupProfiler($container);
	}

	public function setupAssets(Container $container)
	{
		$router = $container->get('alphapup.router');
		$routes = array();
		foreach($container->getConfig('assets')->groups->toArray() as $name => $group) {
			if(isset($group['url'])) {
				$routes[$group['url']] = array(
					'pattern' => $group['url'],
					'controller' => 'Alphapup\\Application\\Controller\\Assets\\Assets',
					'action' => 'index',
					'defaults' => array(
						'group' => $name,
						'type' => $group['type']
					)
				);
			}
		}
		$router->setRoutes($routes);
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