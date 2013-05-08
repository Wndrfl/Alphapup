<?php
namespace Alphapup\Component\Voltron;

use Alphapup\Component\Voltron\Voltron;
use Alphapup\Component\Voltron\VoltronBuilder;
use Alphapup\Component\Voltron\VoltronView;

interface VoltronPluginInterface
{
	public function configureVoltron(VoltronBuilder $voltronBuilder,array $options=array());
	public function name();
	public function pluginForType();
	public function setupView(Voltron $voltron,VoltronView $view);
}