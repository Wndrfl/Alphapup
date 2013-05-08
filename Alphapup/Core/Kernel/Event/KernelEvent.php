<?php
namespace Alphapup\Core\Kernel\Event;

use Alphapup\Core\Event\EventTicket;

abstract class KernelEvent extends EventTicket
{	
	public function __construct($name,$description)
	{
		parent::__construct();
		$this->setName('kernel.'.$name);
		$this->setDescription($description);
	}
}