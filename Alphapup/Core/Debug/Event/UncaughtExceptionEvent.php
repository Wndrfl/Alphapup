<?php
namespace Alphapup\Component\Debug\Event;

use Alphapup\Component\Debug\Event\DebugEvent;

class UncaughtExceptionEvent extends DebugEvent
{
	private
		$_exception;
		
	public function __construct(\Exception $e)
	{
		parent::__construct('uncaught_exception',$e->getMessage());
		$this->setException($e);
	}
	
	public function exception()
	{
		return $this->_exception;
	}
	
	public function setException(\Exception $e)
	{
		$this->_exception = $e;
	}
}