<?php
namespace Alphapup\Component\Debug\Event;

use Alphapup\Core\Event\EventTicket;

abstract class DebugEvent extends EventTicket
{	
	public function __construct($name,$payload)
	{
		parent::__construct();
		$this->setName('debug.'.$name);
		$this->setDescription($payload);
	}
}