<?php
namespace Alphapup\Component\Voltron\Type;

use Alphapup\Component\Voltron\Type\BaseType;
use Alphapup\Component\Voltron\Voltron;
use Alphapup\Component\Voltron\VoltronBuilder;
use Alphapup\Component\Voltron\VoltronCallbackValidator;
use Alphapup\Component\Voltron\VoltronViewInterface;

class Repeated extends BaseType
{	
	public function configureVoltron(VoltronBuilder $voltronBuilder,array $options=array())
	{
		$voltronBuilder
			->add($options['firstName'],$options['type'],$options['options'])
			->add($options['secondName'],$options['type'],$options['options']);
		
		$callback = function(Voltron $voltron) use($options) {
			$firstChild = $voltron->child($options['firstName']);
			$secondChild = $voltron->child($options['secondName']);
			if($firstChild->value() != $secondChild->value()) {
				$error = (isset($options['message'])) ? $options['message'] : 'Must match';
				$voltron->setError($error);
			}
		};
		$voltronBuilder->setValidator(new VoltronCallbackValidator($callback));
	}

	public function defaultOptions()
	{
		return array(
			'type' => 'text',
			'options' => array(),
			'firstName' => 'first',
			'secondName' => 'second'
		);
	}

	public function name()
	{
		return 'repeated';
	}
	
	public function parent()
	{
		return 'field';
	}
}