<?php
namespace LoremIpsum\Plugin;

use Alphapup\Core\DependencyInjection\Container;
use Alphapup\Core\Kernel\PluginInterface;

class Plugin implements PluginInterface
{
	public function boot(Container $container)
	{
		$container->importConfigFile(__DIR__.'/Config.php');
	}
	
	public function dir()
	{
		return realpath(__DIR__.'/..');
	}
}