<?php
namespace Alphapup\Component\Voltron\Type;

use Alphapup\Component\Voltron\Choices\BasicChoices;
use Alphapup\Component\Voltron\Type\BaseType;
use Alphapup\Component\Voltron\Voltron;
use Alphapup\Component\Voltron\VoltronBuilder;
use Alphapup\Component\Voltron\VoltronView;

class Choice extends Field
{		
	public function configureVoltron(VoltronBuilder $voltronBuilder,array $options=array())
	{
		$voltronBuilder
			->setAttribute('choices',$options['choices'])
			->setAttribute('expanded',$options['expanded'])
			->setAttribute('multiple',$options['multiple'])
			->setAttribute('placeholder',$options['placeholder']);
	}
	
	public function defaultOptions()
	{
		return array(
			'choices' => new BasicChoices(array()),
			'expanded' => false,
			'multiple' => false,
			'placeholder' => ''
		);
	}
	
	public function name()
	{
		return 'choice';
	}
	
	public function parent()
	{
		return 'field';
	}
	
	public function setupView(Voltron $voltron,VoltronView $view)
	{
		$view
			->setAttribute('choices',$voltron->attribute('choices')->choices())
			->setAttribute('expanded',$voltron->attribute('expanded'))
			->setAttribute('multiple',$voltron->attribute('multiple'))
			->setAttribute('placeholder',$voltron->attribute('placeholder'));
			
		if($view->attribute('multiple') == true && $view->attribute('expanded') == false) {
			$view->setAttribute('name',$view->attribute('name').'[]');
		}
	}
}