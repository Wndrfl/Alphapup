<?php
namespace Alphapup\Component\Voltron\Type;

use Alphapup\Component\Voltron\Voltron;
use Alphapup\Component\Voltron\Type\BaseType;
use Alphapup\Component\Voltron\VoltronBuilder;
use Alphapup\Component\Voltron\VoltronView;

class Field extends BaseType
{
	public function configureVoltron(VoltronBuilder $voltronBuilder,array $options=array())
	{
		$voltronBuilder
			->setAttribute('attr',$options['attr'])
			->setAttribute('label',$options['label'])
			->setAttribute('value',$options['value']);
	}
	
	public function defaultOptions()
	{
		return array(
			'attr' => array(),
			'disabled' => false,
			'label' => null,
			'message' => 'The value entered is not valid.',
			'value' => null
		);
	}
	
	public function name()
	{
		return 'field';
	}
	
	public function setupView(Voltron $voltron,VoltronView $view)
	{
		$name = $voltron->name();
		
        if($view->hasParent()) {
            $parentName = $view->parent()->attribute('name');
            $name = sprintf('%s[%s]', $parentName, $name);
        }

		$view
			->setAttribute('attr',$voltron->attribute('attr'))
			->setAttribute('label',$voltron->attribute('label'))
			->setAttribute('name',$name)
			->setAttribute('value',$voltron->attribute('value'));
	}
}