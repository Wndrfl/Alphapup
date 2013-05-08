<?php
namespace Alphapup\Core\Kernel\Event;

use Alphapup\Core\Http\Request;
use Alphapup\Core\Kernel\Event\KernelEvent;

class RequestEvent extends KernelEvent
{
	private
		$_request;
		
	public function __construct()
	{
		parent::__construct('request','Request object successfully created.');
	}
}