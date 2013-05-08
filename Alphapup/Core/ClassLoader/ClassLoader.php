<?php
namespace Alphapup\Component\ClassLoader;

class ClassLoader
{
	private
		$_namespaces = array();
		
	private function _loadClass($class)
	{
		if(!$file = $this->findClass($class)) {
			throw new CannotFindClassException($class);
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
	
	public function loadClass($class)
	{
		if(class_exists($class)) {
			return true;
		}
		try {
			$this->_loadClass($class);
			return true;
		}catch(CannotFindClassException $e) {
			return false;
		}
	}
}