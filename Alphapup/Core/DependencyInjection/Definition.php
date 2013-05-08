<?php
namespace Alphapup\Core\DependencyInjection;

class Definition
{
	private $_id;
	private $_params = array();
	
	public function __construct($id,$params=array()) 
	{
		$this->setId($id);
		$this->setParams($params);
	}
	
	public function id()
	{
		return $this->_id;
	}
	
	public function params($key=null,$default=null)
	{
		if(!is_null($key)) {
			return (isset($this->_params[$key])) ? $this->_params[$key] : $default;
		}
		return $this->_params;
	}
	
	public function setId($id)
	{
		$this->_id = $id;
	}
	
	public function setParams($params=array())
	{
		$this->_params = $params;
	}
	
	public function shared()
	{
		return (isset($this->_params['shared']) && $this->_params['shared'] == true) ? true : false;
	}
	
	public function tag($tag)
	{
		return (isset($this->_params['tags'][$tag])) ? $this->_params['tags'][$tag] : false;
	}
	
	public function tags()
	{
		return (isset($this->_params['tags'])) ? $this->_params['tags'] : array();
	}
}