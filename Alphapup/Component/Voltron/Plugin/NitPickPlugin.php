<?php
namespace Alphapup\Component\Voltron\Plugin;

use Alphapup\Component\NitPick\NitPick;
use Alphapup\Component\Voltron\Plugin\BasePlugin;
use Alphapup\Component\Voltron\Voltron;
use Alphapup\Component\Voltron\VoltronCallbackValidator;
use Alphapup\Component\Voltron\VoltronBuilder;

class NitPickPlugin extends BasePlugin
{
	private
		$_nitPick;
		
	public function __construct(NitPick $nitPick)
	{
		$this->setNitPick($nitPick);
	}
	
	public function configureVoltron(VoltronBuilder $voltronBuilder,array $options=array())
	{
		$voltronBuilder->setAttribute('nitpick',$options['nitpick']);
		$nitpick = $this->_nitPick;
		$callback = function(Voltron $voltron) use ($nitpick) {
			
			if(!$voltron->attribute('nitpick')) {
				return;
			}
			if(!is_array($voltron->attribute('nitpick'))) {
				// DO EXCEPTION
				return;
			}

			// Setup nitpick tester
			$tester = $nitpick->tester($voltron);
			
			// Set up tests from requested rules
			foreach($voltron->attribute('nitpick') as $rule => $meta) {
				
				// Setup tongue message
				$message = (isset($meta['message'])) ? $meta['message'] : null;
				$params = array(
					'label' => $voltron->attribute('label'),
					'name' => $voltron->name(),
					'placeholder' => $voltron->attribute('placeholder'),
					'value' => $voltron->value(),
				);
				$params = (isset($meta['messageParams'])) ? array_merge($params,$meta['messageParams']) : $params;
				
				// Setup test
				$tester->testMethod(array($voltron,'value'),$rule,array(
					'message' => $message,
					'messageParams' => $params
				));
			}
			
			// Run tests and report errors
			$results = $nitpick->runTests($tester);
			foreach($results->errors() as $error) {
				$voltron->setError($error->message());
			}
		};
		$voltronBuilder->setValidator(new VoltronCallbackValidator($callback));
	}
	
	public function defaultOptions()
	{
		return array(
			'nitpick' => array()
		);
	}
	
	public function name()
	{
		return 'nitpick';
	}
	
	public function pluginForType()
	{
		return 'field';
	}
	
	public function setNitPick(NitPick $nitPick)
	{
		$this->_nitPick = $nitPick;
	}
}