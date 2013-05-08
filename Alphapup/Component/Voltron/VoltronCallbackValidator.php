<?php
namespace Alphapup\Component\Voltron;

use Alphapup\Component\Voltron\Voltron;
use Alphapup\Component\Voltron\VoltronValidatorInterface;

class VoltronCallbackValidator implements VoltronValidatorInterface
{
	private
		$_callback;
		
	public function __construct($callback)
	{
		$this->_callback = $callback;
	}
	
	public function validate(Voltron $voltron)
	{
		return call_user_func($this->_callback,$voltron);
	}
}