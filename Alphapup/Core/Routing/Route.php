<?php
namespace Alphapup\Core\Routing;

class Route
{
	private
		$_action,
		$_alias,
		$_controller,
		$_defaults = array(),
		$_defaultRegex = '[A-Za-z0-9_-]+', // letters, numbers, underscores, dashes
		$_pattern,
		$_preparedPattern,
		$_requirements,
		$_slugCounter = 0,
		$_slugExp = '|\{([A-Za-z0-9_]+)\}|', // something like {slug}
		$_slugs = array();
		
	public function __construct($alias,$route=array())
	{
		$this->_alias = $alias;
		$this->_pattern = (isset($route['pattern'])) ? $route['pattern'] : null;
		$this->_controller = (isset($route['controller'])) ? $route['controller'] : null;
		$this->_action = (isset($route['action'])) ? $route['action'] : null;
		$this->_defaults = (isset($route['defaults'])) ? $route['defaults'] : array();
		$this->_requirements = (isset($route['requirements'])) ? $route['requirements'] : array();
		$this->_prepareRoute();
	}
	
	private function _prepareRoute()
	{	
		$pattern = preg_replace_callback($this->_slugExp,array($this,'_prepareMatches'),$this->_pattern);
		$this->_preparedPattern = $pattern;
		return $this->_preparedPattern;
	}
	
	private function _prepareMatches($matches)
	{
		$requirements = $this->_requirements;
		$slugName = $matches[1]; // something like slug
		
		if(isset($requirements[$slugName])) {
			$replacement = $requirements[$slugName];
		}else{
			$replacement = $this->_defaultRegex;
		}
		
		$this->_slugCounter++;
		$this->_slugs[$slugName] = array(
			'slugPosition' => $this->_slugCounter,
			'regex' => '('.$replacement.')'
		);
		
		return $this->_slugs[$slugName]['regex'];
	}
	
	public function action()
	{
		return $this->_action;
	}
	
	public function alias()
	{
		return $this->_alias;
	}
	
	public function buildRoute($params=array(),$get=array())
	{
		$url = $this->_pattern;		
		$url = preg_replace_callback($this->_slugExp,function($matches) use ($params) {
			$slugName = $matches[1];
			return (isset($params[$slugName])) ? $params[$slugName] : '';
		},$url);
		
		if($get) {
			$url .= '?';
			$parts = array();
			foreach($get as $k => $v) {
				$parts[] = $k.'='.$v;
			}
			$url.= implode('&',$parts);
		}
		
		return $url;
	}
	
	public function controller()
	{
		return $this->_controller;
	}
	
	public function matchPattern($url)
	{
		if(preg_match('#^'.$this->_preparedPattern.'$#',$url,$matches)) {
			$variables = array();
			foreach($this->_slugs as $slug => $data) {
				if(isset($matches[$data['slugPosition']])) {
					$variables[$slug] = $matches[$data['slugPosition']];
				}
			}
			foreach($this->_defaults as $name => $default) {
				if(!isset($variables[$name])) {
					$variables[$name] = $default;
				}
			}
			return $variables;
		}
		return false;
	}
	
	public function pattern()
	{
		return $this->_pattern;
	}
}