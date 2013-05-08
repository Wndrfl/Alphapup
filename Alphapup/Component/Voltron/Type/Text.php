<?php
namespace Alphapup\Component\Voltron\Type;

use Alphapup\Component\Voltron\Type\BaseType;
use Alphapup\Component\Voltron\Voltron;
use Alphapup\Component\Voltron\VoltronBuilder;
use Alphapup\Component\Voltron\VoltronView;

class Text extends BaseType
{
	public function configureVoltron(VoltronBuilder $voltronBuilder,array $options=array())
	{
		$voltronBuilder
			->setAttribute('pattern',$options['pattern'])
			->setAttribute('placeholder',$options['placeholder']);
	}
	
	public function defaultOptions()
	{
		return array(
			'pattern' => null,
			'placeholder' => null,
		);
	}
	
	public function name()
	{
		return 'text';
	}
	
	public function parent()
	{
		return 'input';
	}
	
	public function setupView(Voltron $voltron,VoltronView $view)
	{
		$view
			->setAttribute('pattern',$voltron->attribute('pattern'))
			->setAttribute('placeholder',$voltron->attribute('placeholder'));
	}
}