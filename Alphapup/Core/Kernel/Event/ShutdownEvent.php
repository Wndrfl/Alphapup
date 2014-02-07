<?php
namespace Alphapup\Core\Kernel\Event;

use Alphapup\Core\Http\Request;
use Alphapup\Core\Http\Response;
use Alphapup\Core\Kernel\Event\KernelEvent;

class ShutdownEvent extends KernelEvent
{
	private
		$_request,
		$_response;
		
	public function __construct(Request $request,Response $response)
	{
		parent::__construct('shutdown','The kernel is shutting down.');
		$this->_request = $request;
		$this->_response = $response;
	}
	
	public function request()
	{
		return $this->_request;
	}
	
	public function response()
	{
		return $this->_response;
	}
}