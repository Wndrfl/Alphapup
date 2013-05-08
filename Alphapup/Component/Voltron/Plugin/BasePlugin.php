<?php
namespace Alphapup\Component\Voltron\Plugin;

use Alphapup\Component\Voltron\Voltron;
use Alphapup\Component\Voltron\VoltronBuilder;
use Alphapup\Component\Voltron\VoltronPluginInterface;
use Alphapup\Component\Voltron\VoltronView;

abstract class BasePlugin implements VoltronPluginInterface
{
	public function configureVoltron(VoltronBuilder $voltronBuilder,array $options=array())
	{
		return false;
	}
	
	public function setupView(Voltron $voltron,VoltronView $view)
	{
		
	}
}