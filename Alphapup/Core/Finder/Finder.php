<?php
namespace Alphapup\Core\Finder;

use Alphapup\Core\Finder\FinderFile;

class Finder
{
	private $_files = array();
	
	private function _get($path)
	{
		if(isset($this->_files[$path])) {
			return $this->_files[$path];
		}
		$contents = false;
		if(!file_exists($path)) {
			return false;
		}
		if($f = @fopen($path,'r')) {
			$size = filesize($path);
			$contents = $size ? fread($f,$size) : $contents;
			fclose($f);
			
			$file = $this->newFile($path,$contents);
			$this->_files[$path] = $file;
			return $file;
		}
		return false;
	}
	
	public function fileNames($dir)
	{
		$dh = opendir($dir);
		$entries = array();
	    while(false !== $entry = readdir($dh)) {
			if($entry != "." && $entry != "..") {
				$entries[] = $entry;
			}
		}
		closedir($dh);
		return $entries;
	}
	
	public function get($path)
	{
		if(is_array($path)) {
			$files = array();
			foreach($path as $file) {
				$files[] = $this->_get($file);
			}
			return $files;
		}else{
			return $this->_get($path);
		}
	}
	
	public function newFile($path,$contents)
	{
		$file = new FinderFile($path,$contents);
		return $file;
	}
	
	public function mirror($source,$dest,$unlink=true)
	{
		if($unlink) {
			$this->unlink($dest);
		}
		
	    if(is_file($source)) {
	        return copy($source, $dest);
	    }

	    if(!is_dir($dest)) {
	        $this->mkdir($dest);
	    }
	
	    foreach($this->fileNames($source) as $entry) {
	        $this->mirror("$source/$entry", "$dest/$entry",false);
	    }

	    return true;
	}
	
	public function mkdir($path,$perm=0755,$recursive=true)
	{
		return mkdir($path,$perm,$recursive);
	}
	
	public function put(FinderFile $file)
	{
		if($f = @fopen($file->path(),'w')) {
			fwrite($f,$file->contents());
			$this->_files[$file->path()] = $file;
			fclose($f);
			return true;
		}
		return false;
	}
	
	public function symlink($target,$link)
	{
		if(!is_dir($target)) {
			return false;
		}

		$this->unlink($link);
		symlink($target,$link);
	}
	
	public function unlink($str)
	{
		if(is_link($str)) {
			unlink($str);
		}
		if(is_file($str)){
            return @unlink($str);
		}elseif(is_dir($str)) {
			$str = rtrim($str,'/');
            foreach($this->fileNames($str) as $entry) {
                $this->unlink($str.'/'.$entry);
            }
			if(is_link($str)) {
				unlink($str);
			}
            return rmdir($str);
        }
	}
}