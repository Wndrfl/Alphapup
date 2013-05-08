<?php 
namespace Alphapup\Core\Cache;

use Alphapup\Core\Finder\Finder;
use Alphapup\Core\Kernel\Kernel;

class Cache
{	
	private
		$_cacheDir,
		$_finder;
	
	public function __construct(Finder $finder,$cacheDir)
	{
		$this->setFinder($finder);
		$this->setCacheDir($cacheDir);
	}
	
	public function _get($path)
	{
		if(!$cache = $this->_finder->get($path)) {
			return false;
		}
		return unserialize(gzinflate($cache->contents()));
	}
	
	public function cacheDir() {
		return $this->_cacheDir;
	}
	
	public function get($id)
	{	
		$path = $this->cacheDir().$id;
		if(!$c = $this->_get($path)) {
			return false;
		}
		if(time() < $c[0]) {  
			return $c[1];
		}else{
			unlink($path);
			return false;
		}
	}
	
	public function hash($value)
	{
		return str_pad(base_convert(
			sprintf('%u',crc32($value)),10,36),7,'0',STR_PAD_LEFT);
	}
	
	public function set($id,$content,$ttl=3600)
	{
		if(!is_dir($this->_cacheDir)) {
			mkdir($this->_cacheDir,0755);
		}
		$ttl = (is_numeric($ttl)) ? $ttl : 3600;
		$data = gzdeflate(serialize(array((time()+$ttl),$content)));
		if(!is_dir($this->cacheDir())) {
			$this->_finder->mkdir($this->cacheDir());
		}
		$file = $this->_finder->newFile($this->cacheDir().'/'.$id,$data);
		$this->_finder->put($file);
	}
	
	public function setCacheDir($path)
	{
		$this->_cacheDir = $path;
	}
	
	public function setFinder(Finder $finder) {
		$this->_finder = $finder;
	}
}