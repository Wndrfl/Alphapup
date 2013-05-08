<?php
namespace Alphapup\Component\Debug\DataCollector;

use Alphapup\Component\Debug\DataCollector\BaseDataCollector;
use Alphapup\Core\Event\EventCenter;

class EventsDataCollector extends BaseDataCollector
{
	private
		$_eventCenter,
		$_fired,
		$_notFired;
		
	public function __construct(EventCenter $eventCenter)
	{
		$this->setEventCenter($eventCenter);
	}
	
	public function collect()
	{
		$this->_fired = $this->_eventCenter->fired();
		$this->_notFired = $this->_eventCenter->notFired();
		unset($this->_eventCenter);
	}
	
	public function fired()
	{
		return $this->_fired;
	}
	
	public function name()
	{
		return 'events';
	}
	
	public function notFired()
	{
		return $this->_notFired;
	}
	
	public function setEventCenter(EventCenter $eventCenter)
	{
		$this->_eventCenter = $eventCenter;
	}
}