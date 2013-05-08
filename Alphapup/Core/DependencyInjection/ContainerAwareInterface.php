<?php
namespace Alphapup\Core\DependencyInjection;

use Alphapup\Core\DependencyInjection\Container;

interface ContainerAwareInterface
{
	public function setContainer(Container $container);
}