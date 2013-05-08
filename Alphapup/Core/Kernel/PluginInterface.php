<?php
namespace Alphapup\Core\Kernel;

use Alphapup\Core\DependencyInjection\Container;

interface PluginInterface
{
	public function boot(Container $container);
}