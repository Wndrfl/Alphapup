<?php
namespace Alphapup\Core\Event;

use Alphapup\Core\Event\EventListener;
use Alphapup\Core\Event\EventTicket;

class EventCenter
{
	private
		$_fired = array(),
		$_listeners = array();
	
	private function _createEvent($caller,$name,$message)
	{
		$event = new EventTicket();
		$event->caller($caller);
		$event->name($name);
		$event->message($message);
		return $event;
	}
	
	public function addListener($event,$callback)
	{
		if(!isset($this->_listeners[$event])) {
			$this->_listeners[$event] = array();
		}
		$this->_listeners[$event][] = $callback;
	}
	
	public function fire(EventTicket $event)
	{
		$this->_fired[] = $event;
		
		if(isset($this->_listeners[$event->name()])) {
			foreach($this->_listeners[$event->name()] as $callback) {
				call_user_func_array($callback,array($event));
			}
		}
	}
	
	public function fired()
	{
		return $this->_fired;
	}
	
	public function notFired()
	{
		$allEvents = array_keys($this->_listeners);
		$fired = array();
		foreach($this->_fired as $event) {
			$fired[] = $event->name();
		}
		$notFired = array();
		foreach($allEvents as $event) {
			if(!in_array($event,$fired)) {
				$notFired[] = $event;
			}
		}
		return $notFired;
	}
}