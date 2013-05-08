<?php
namespace Alphapup\Component\Helper;

use Alphapup\Component\Helper\BaseHelper;
use Alphapup\Core\Http\Request;
use Alphapup\Core\Routing\Router;

class UrlHelper extends BaseHelper
{
	private
		$_request,
		$_router;
		
	public function __construct(Request $request,Router $router)
	{
		$this->setRequest($request);
		$this->setRouter($router);
	}
	
	public function absoluteUrl($uri=null)
	{
		if(is_null($uri)) {
			$uri = '';
		}
		return $this->_request->fullHost().'/'.ltrim($uri,'/');
	}	
	
	function deriveUrl($get=array())
	{
		$get = array_merge($_GET,$get);
		$parts = array();
		foreach($get as $k => $v) {
			$parts[] = $k.'='.$v;
		}
		
		$path = explode('?',$_SERVER["REQUEST_URI"]);
		if(count($parts) == 0) {
			return $this->absoluteUrl($path[0]);
		}
		return $this->absoluteUrl($path[0].'?'.implode('&',$parts));
	}
	
	public function name()
	{
		return 'url';
	}
	
	public function sanitize($string, $force_lowercase = true, $anal = false)
	{
	    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
	                   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
	                   "â€”", "â€“", ",", "<", ".", ">", "/", "?");
	    $clean = trim(str_replace($strip, "", strip_tags($string)));
	    $clean = preg_replace('/\s+/', "-", $clean);
	    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
	    return ($force_lowercase) ?
	        (function_exists('mb_strtolower')) ?
	            mb_strtolower($clean, 'UTF-8') :
	            strtolower($clean) :
	        $clean;
	}
	
	public function setRequest(Request $request)
	{
		$this->_request = $request;
	}
	
	public function setRouter(Router $router)
	{
		$this->_router = $router;
	}
	
	public function slug($string, $slug = '-', $extra = null)
	{
		// unaccent first
		if(strpos($string = htmlentities($string, ENT_QUOTES, 'UTF-8'), '&') !== false) {
			$string = html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|caron|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1', $string), ENT_QUOTES, 'UTF-8');
		}
		return strtolower(trim(preg_replace('~[^0-9a-z' . preg_quote($extra, '~') . ']+~i', $slug, $string), $slug));
	}
	
	public function url($alias,$params=array(),$get=array(),$absolute=true)
	{
		if(!$route = $this->_router->getByAlias($alias)) {
			return false;
		}
		if(!is_array($params)) {
			$absolute = (bool) $params;
			$params = array();
		}
		$route = $route->buildRoute($params,$get);
		if($absolute == true) {
			return $this->absoluteUrl($route);
		}
		return $route;
	}
}