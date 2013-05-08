<?php
namespace Alphapup\Core\ClassLoader;

use Alphapup\Core\ClassLoader\Exception\CannotFindClassException;

class UniversalClassLoader
{
	private
		$_namespaces = array(),
		$_triggerError;
	
	public function __construct($triggerError=true)
	{
		$this->_triggerError = $triggerError;
	}
	
	private function _loadClass($class)
	{
		if(!$file = $this->findClass($class)) {
			return false;
		}
		require $file;
	}
	
	public function findClass($class)
	{		
		if($class[0] == '\\') {
			$class = substr($class,1);
		}
		$pos = strrpos($class,'\\');
		$namespace = substr($class,0,$pos);
		foreach($this->_namespaces as $ns => $dirs) {
			if(strpos($namespace,$ns) !== 0) {
				continue;
			}
			foreach($dirs as $dir) {
				$className = substr($class,$pos+1);
				$file = $dir.DIRECTORY_SEPARATOR.str_replace('\\',DIRECTORY_SEPARATOR,$namespace).DIRECTORY_SEPARATOR.str_replace('_', '/', $className).'.php';
				if(file_exists($file)) {
					return $file;
				}
			}
		}
	}
	
	public function loadClass($class) {
		return	$this->_loadClass($class);
	}
	
	public function registerNamespace($namespace,$dirs=array()) {
		$dirs = (is_array($dirs)) ? $dirs : array($dirs);
		$this->_namespaces[$namespace] = $dirs;
	}
	
	public function registerNamespaces($namespaces=array()) {
		foreach($namespaces as $ns => $dir) {
			$this->registerNamespace($ns,$dir);
		}
	}
}