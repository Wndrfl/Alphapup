<?php
namespace Alphapup\Core\Http;

class Request
{
	private $_actionName;
	private $_controllerName;
	private $_params = array();
	private $_requestUri;
	private $_url;
	
	public function __construct() {
		$this->setUrl();
	}
	
	public function fullHost()
	{
		return $this->_url['protocol'].$this->_url['host'];
	}
	
	public function getActionName()
	{
		return (!empty($this->_actionName)) ? $this->_actionName : false;
	}

	public function getControllerName()
	{
		return (!empty($this->_controllerName)) ? $this->_controllerName : false;
	}
	
	public function getCookie($key=null,$default=null)
	{
		if(is_null($key)) {
			return $_COOKIE;
		}
		return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : $default;
	}
	
	public function getHeader($key)
	{

        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_'.strtoupper(str_replace('-','_',$key));
        if(isset($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }

        // This seems to be the only way to get the Authorization header on
        // Apache
        if(function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers[$key])) {
                return $headers[$key];
            }
            $key = strtolower($key);
            foreach ($key as $k => $v) {
                if (strtolower($k) == $key) {
                    return $v;
                }
            }
        }

        return false;
	}
	
	public function getHeaders()
	{
		if(function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            return $headers;
        }
	}
	
	public function getMethod()
	{
		return $this->getServer('REQUEST_METHOD');
	}
	
	public function getParam($key)
	{
		if(isset($this->_params[$key])) {
			return $this->_params[$key];
		}
	}
	
	public function getParams()
	{
		return $this->_params;
	}
	
	public function getPost($key=null,$default=null)
	{
		if(is_null($key)) {
			return $_POST;
		}
		return (isset($_POST[$key])) ? $_POST[$key] : $default;
	}
	
	public function getQuery($key=null,$default=null)
	{
		if(is_null($key)) {
			return $_GET;
		}
		return (isset($_GET[$key])) ? $_GET[$key] : $default;
	}
	
	public function getServer($part=null,$default=null)
	{
		if(is_null($part)) {
			return $_SERVER;
		}
		return (isset($_SERVER[$part])) ? $_SERVER[$part] : $default;
	}
	
	public function getUrl($part=null)
	{
		if(empty($this->_url)) {
			$this->setUrl();
		}
		return (is_null($part)) ? $this->_url['url'] : (isset($this->_url[$part])) ? $this->_url[$part] : false;
	}
	
	public function ip()
	{
		return $this->getServer('REMOTE_ADDR');
	}
	
	public function isAjax()
    {
        return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
    }
	
	public function isGet()
	{
		return ($this->getMethod() == 'GET');
	}
	
	public function isPost()
	{
		return ($this->getMethod() == 'POST');
	}
	
	public function setActionName($name)
	{
		$this->_actionName = $name;
		return $this;
	}
	
	public function setControllerName($name)
	{
		$this->_controllerName = $name;
		return $this;
	}
	
	public function setParam($key,$val)
	{
		$this->_params[$key] = $val;
		return $this;
	}
	
	public function setParams($params=array())
	{
		if(!is_array($params)) {
			return $this;
		}
		$this->_params = $params;
		return $this;
	}
	
	public function setPost($key,$val)
	{
		$_POST[$key] = $val;
		return $this;
	}
	
	public function setQuery($key,$val)
	{
		$_GET[$key] = $val;
		return $this;
	}
	
	public function setUrl()
	{
		$url = array();
		$url['protocol'] = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$url['host'] = $_SERVER['HTTP_HOST'];
		$domain_parts = explode('.',$url['host']);
		$url['tld'] = array_pop($domain_parts);
		$url['domain'] = array_pop($domain_parts).'.'.$url['tld'];
		$url['subdomain'] = (count($domain_parts) > 0) ? implode('.',$domain_parts) : null;
		$url['uri'] = $_SERVER['REQUEST_URI'];
		$url['url'] = $url['protocol'].$url['host'].$url['uri'];
		$this->_url = $url;
		return $this;
	}
}