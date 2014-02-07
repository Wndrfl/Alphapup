<?php
namespace Alphapup\Core\Kernel\Event;

use Alphapup\Core\Http\Response;
use Alphapup\Core\Kernel\Event\KernelEvent;

class ResponseEvent extends KernelEvent
{
	private
		$_response;
		
	public function __construct(Response $response)
	{
		parent::__construct('response','Response object successfully created.');
		$this->_response = $response;
	}
	
	public function response() 
	{
		return $this->_response;
	}
}