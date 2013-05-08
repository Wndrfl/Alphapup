<?php
namespace Alphapup\Component\Debug;

use Alphapup\Component\Debug\DataCollector\DataCollectorInterface;

class Profile
{
	private
		$_collectors = array(),
		$_id,
		$_ip,
		$_time,
		$_url;
		
	public function __construct($id)
	{
		$this->_id = $id;
	}
	
	public function collector($name)
	{
		return (isset($this->_collectors[$name])) ? $this->_collectors[$name] : false;
	}
	
	public function id()
	{
		return $this->_id;
	}
	
	public function ip()
	{
		return $this->_ip;
	}
		
	public function setCollector(DataCollectorInterface $dataCollector)
	{
		$this->_collectors[$dataCollector->name()] = $dataCollector;
	}
	
	public function setIp($ip)
	{
		$this->_ip = $ip;
	}
	
	public function setTime($time)
	{
		$this->_time = $time;
	}
	
	public function setUrl($url)
	{
		$this->_url = $url;
	}
	
	public function time()
	{
		return $this->_time;
	}
	
	public function url()
	{
		return $this->_url;
	}
}