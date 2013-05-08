<?php
namespace Alphapup\Component\Voltron;

use Alphapup\Component\Voltron\Voltron;
use Alphapup\Component\Voltron\VoltronBuilder;
use Alphapup\Component\Voltron\VoltronPluginInterface;
use Alphapup\Component\Voltron\VoltronView;

interface VoltronTypeInterface
{
	public function configureVoltron(VoltronBuilder $voltronBuilder,array $options=array());
	public function defaultOptions();
	public function name();
	public function parent();
	public function setPlugin(VoltronPluginInterface $plugin);
	public function setupView(Voltron $voltron,VoltronView $view);
}