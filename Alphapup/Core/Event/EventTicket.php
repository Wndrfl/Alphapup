<?php
namespace Alphapup\Core\Event;

use Alphapup\Core\Event\EventTicketInterface;

class EventTicket implements EventTicketInterface
{
	private 
		$_caller,
		$_description,
		$_name,
		$_timestamp;
		
	public function __construct() {
		$this->setTimestamp(time());
	}

	public function description()
	{
		return $this->_description;
	}
	
	public function name() 
	{
		return $this->_name;
	}
	
	public function setDescription($description)
	{
		$this->_description = $description;
	}
	
	public function setName($name)
	{
		$this->_name = $name;
	}
	
	public function setTimestamp($timestamp)
	{
		$this->_timestamp = $timestamp;
	}
	
	public function timestamp()
	{
		return $this->_timestamp;
	}
}