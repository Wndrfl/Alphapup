<?php
namespace Alphapup\Core\Routing;

use Alphapup\Core\Http\Request;
use Alphapup\Core\Routing\Route;

class Router
{
	private $_routes;
	
	public function __construct($routes=array())
	{
		$this->setRoutes($routes);
	}
	
	public function getByAlias($alias)
	{
		return (isset($this->_routes[$alias])) ? $this->_routes[$alias] : false;
	}
	
	public function getByPattern($pattern)
	{
		foreach($this->routes() as $alias => $route) {
			if($route->pattern() == $pattern) {
				return $route;
			}
		}
		return false;
	}
	
	public function route(Request $request)
	{	
		$uri = explode('?',$request->getUrl('uri'));
		$uri = $uri[0];
		$check = ltrim($uri,'/');
		
		// no uri
		if($check == '') {
			$params = array();

		// check for a direct match
		}elseif($route = $this->getByPattern($check)) {
			$params = $route->matchPattern($check);

		// check for custom routing
		}else{
			foreach($this->_routes as $k => $v) {
				$params = $v->matchPattern($check);
				if($params !== false) {
					$route = $v;
					break;
				}
			}
		}

		// no custom routes found, use literal
		$route = (!empty($route)) ? $route : null;
		
		$c = ($route) ? $route->controller() : null;
		$request->setControllerName($c);
		
		$a = ($route) ? $route->action() : null;
		$request->setActionName($a);

		// apply params
		if($params) {
			foreach($params as $k => $v) {
				$request->setParam($k,$v);
			}
		}
		
		return $request;
	}
	
	public function routes()
	{
		return $this->_routes;
	}
	
	public function setRoutes($routes=array())
	{
		foreach($routes as $alias => $route) {
			$this->_routes[$alias] = new Route($alias,$route);
		}
	}
}