<?php
namespace Alphapup\Component\Voltron\Type;

use Alphapup\Component\Voltron\Type\BaseType;
use Alphapup\Component\Voltron\Voltron;
use Alphapup\Component\Voltron\VoltronBuilder;
use Alphapup\Component\Voltron\VoltronView;

class Hidden extends BaseType
{
	public function configureVoltron(VoltronBuilder $voltronBuilder,array $options=array())
	{
	}
	
	public function defaultOptions()
	{
		return array(
		);
	}
	
	public function name()
	{
		return 'hidden';
	}
	
	public function parent()
	{
		return 'input';
	}
	
	public function setupView(Voltron $voltron,VoltronView $view)
	{
	}
}