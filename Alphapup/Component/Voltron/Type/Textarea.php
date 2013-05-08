<?php
namespace Alphapup\Component\Voltron\Type;

use Alphapup\Component\Voltron\Type\BaseType;
use Alphapup\Component\Voltron\Voltron;
use Alphapup\Component\Voltron\VoltronBuilder;
use Alphapup\Component\Voltron\VoltronView;

class Textarea extends BaseType
{
	public function configureVoltron(VoltronBuilder $voltronBuilder,array $options=array())
	{
		$voltronBuilder
			->setAttribute('placeholder',$options['placeholder']);
	}
	
	public function defaultOptions()
	{
		return array(
			'placeholder' => null
		);
	}
	
	public function name()
	{
		return 'textarea';
	}
	
	public function parent()
	{
		return 'field';
	}
	
	public function setupView(Voltron $voltron,VoltronView $view)
	{
		$view
			->setAttribute('placeholder',$voltron->attribute('placeholder'));
	}
}