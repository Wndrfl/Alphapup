<?php
namespace Alphapup\Component\Voltron\Type;

use Alphapup\Component\Voltron\Type\BaseType;
use Alphapup\Component\Voltron\VoltronBuilder;
use Alphapup\Component\Voltron\Voltron;
use Alphapup\Component\Voltron\VoltronView;

class Input extends BaseType
{		
	public function configureVoltron(VoltronBuilder $voltronBuilder,array $options=array())
	{
		$voltronBuilder
			->setAttribute('autofocus',$options['autofocus'])
			->setAttribute('autocomplete',$options['autocomplete'])
			->setAttribute('max',$options['max'])
			->setAttribute('maxlength',$options['maxlength'])
			->setAttribute('min',$options['min'])
			->setAttribute('multiple',$options['multiple'])
			->setAttribute('required',$options['required'])
			->setAttribute('step',$options['step'])
			->setAttribute('value',$options['value']);
	}
	
	public function defaultOptions()
	{
		return array(
			'autofocus' => null,
			'autocomplete' => null,
			'max' => null,
			'maxlength' => null,
			'min' => null,
			'multiple' => null,
			'required' => false,
			'step' => false,
			'value' => null
		);
	}
	
	public function name()
	{
		return 'input';
	}
	
	public function parent()
	{
		return 'field';
	}
	
	public function setupView(Voltron $voltron,VoltronView $view)
	{
		$view
			->setAttribute('autocomplete',$voltron->attribute('autocomplete'))
			->setAttribute('autofocus',$voltron->attribute('autofocus'))
			->setAttribute('max',$voltron->attribute('max'))
			->setAttribute('maxlength',$voltron->attribute('maxlength'))
			->setAttribute('min',$voltron->attribute('min'))
			->setAttribute('multiple',$voltron->attribute('multiple'))
			->setAttribute('required',$voltron->attribute('required'))
			->setAttribute('step',$voltron->attribute('step'))
			->setAttribute('value',$voltron->attribute('value'));
	}
}