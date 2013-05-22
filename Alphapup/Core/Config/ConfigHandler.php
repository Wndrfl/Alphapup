<?php
namespace Alphapup\Core\Config;

class ConfigHandler
{
	private $_config;

	public function __construct($config=array())
	{
		$this->import($config);
	}
	
	public function __get($key)
	{
		return $this->get($key);
	}
	
	protected function _arrayMergeRecursive($first,$second)
	{
        if(is_array($first) && is_array($second)) {
            foreach($second as $key => $value) {
                if(isset($first[$key])) {
                    $first[$key] = $this->_arrayMergeRecursive($first[$key], $value);
                }else{
                    if($key === 0) {
                        $first= array(0=>$this->_arrayMergeRecursive($first, $value));
                    }else{
                        $first[$key] = $value;
                    }
                }
            }
        }else{
            $first = $second;
        }
        return $first;
    }
	
	private function _import($key,$value,$overwrite=false)
	{
		if(!is_array($this->_config)) {
			$this->_config = array();
		}
		$key = strtolower($key);
		if(!$overwrite && isset($this->_config[$key])) {
			return;
		}
		if(is_array($value)) {
			$this->_config[$key] = new self($value);
		}else{
			$this->_config[$key] = $value;
		}
	}
	
	public function add($key,$value=array(),$overwrite=false)
	{
		$this->_import($key,$value,$overwrite);
		return $this->get($key);
	}
	
	public function compile()
	{
		$config = $this->toArray();
		foreach($config as $k => $v) {
			$config[$k] = $this->compileRecursive($config,$v);
		}
		$this->reset();
		$this->import($config);
	}
	
	public function compileRecursive($config=array(),$part)
	{
		if(is_array($part)) {
			$segments = array();
			foreach($part as $k => $v) {
				$segments[$k] = $this->compileRecursive($config,$v);
			}
			return $segments;
		}
		$part = $this->replaceVariablesRecursive($config,$part);
		return $part;
	}
	
	public function get($key,$default=null,$parent=null)
	{
		if(!isset($this->_config[$key])) {
			return $default;
		}
		$value = $this->_config[$key];
		if($value instanceof ConfigHandler) {
			return $value;
		}
		$current = $this;
		$parent = (is_null($parent)) ? $this : $parent;
		$value = preg_replace_callback('|%([A-Za-z0-9.]+)%|',function($matches) use ($current,$parent,$default) {
			$parts = explode('.',$matches[1]);
			foreach($parts as $part) {
				$current = $parent->get($part,$default,$parent);
			}
			return $current;
		},$value);
		return $value;
	}
	
	public function import($config=array())
	{
		// merge?
		if(!empty($this->_config)) {
			$first = $this->toArray();
			$config = $this->_arrayMergeRecursive($first,$config);
		}
		
		foreach($config as $k => $v) {
			$this->_import($k,$v,true);
		}
	}
	
	public function replaceVariablesRecursive($config=array(),$part) 
	{
		// import array
		if(!is_array($part)) {
			preg_match_all('|%([A-Za-z0-9._-]+)%|',$part,$matches,PREG_SET_ORDER);
			if(isset($matches[0])) {
				foreach($matches as $match) {
					$parts = explode('.',$match[1]);
					$c = $config;
					foreach($parts as $p) {
						if(isset($c[$p])) {
							$c = $c[$p];
						}else{
							return array(); // doesn't exist in config...return blank
						}
					}
					if(is_array($c)) {
						$part = $c; // $part might be an array now
					}
				}
			}
		}
		if(is_array($part)) {
			foreach($part as $k => $v) {
				$part[$k] = $this->replaceVariablesRecursive($config,$v);
			}
			return $part;
		}
		$part = $this->replaceVariables($config,$part);
		return $part;
	}
	
	public function replaceVariables($config=array(),$part)
	{
		$handler = $this;
		// string replacement
		$part = preg_replace_callback('|%([A-Za-z0-9._-]+)%|',function($matches) use ($handler,$config) {
			$parts = explode('.',$matches[1]);
			$c = $config;

			foreach($parts as $part) {
				if(isset($c[$part])) {
					$c = $c[$part];
				}
			}
			
			return (!is_array($c)) ? $handler->replaceVariables($config,$c) : 'Array';
			
		},$part);
		return $part;
	}
	
	public function reset() {
		$this->_config = array();
	}
	
	public function toArray()
	{
		if(empty($this->_config)) {
			return array();
		}
		
		$config = array();
		foreach($this->_config as $k => $v) {
			if($v instanceof ConfigHandler) {
				$config[$k] = $v->toArray();
			}else{
				$config[$k] = $v;
			}
		}
		return $config;
	}
}