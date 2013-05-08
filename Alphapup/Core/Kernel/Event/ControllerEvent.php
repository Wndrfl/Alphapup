<?php
namespace Alphapup\Core\Kernel\Event;

use Alphapup\Core\Kernel\Event\KernelEvent;

class ControllerEvent extends KernelEvent
{
	private
		$_request;
		
	public function __construct()
	{
		parent::__construct('controller','Controller object successfully created.');
	}
}