<?php
namespace Alphapup\Component\Asset;

use Alphapup\Component\Asset\Exception\CantOpenFileException;
use Alphapup\Component\Asset\AssetInterface;

class FileAsset implements AssetInterface
{
	private
		$_content,
		$_filtered,
		$_path,
		$_pathinfo;
		
	public function __construct($path)
	{
		$this->setPath($path);
	}
	
	public function content()
	{
		if(empty($this->_content)) {
			if(!file_exists($this->_path) || !$fh = @fopen($this->_path,'r')) {
				throw new CantOpenFileException($this->_path);
			}
			$size = filesize($this->_path);
			$content = $size ? fread($fh,$size) : '';
			fclose($fh);
			$this->_content = $content;
		}
		return $this->_content;
	}
	
	public function dir()
	{
		$pathinfo = $this->pathinfo();
		return $pathinfo['dirname'];
	}
	
	public function extension()
	{
		$pathinfo = $this->pathinfo();
		if(!isset($pathinfo['extension'])) {
			return false;
		}
		return $pathinfo['extension'];
	}
	
	public function path()
	{
		return $this->_path;
	}
	
	public function pathinfo()
	{
		if(!empty($this->_pathinfo)) {
			return $this->_pathinfo;
		}
		$this->_pathinfo = pathinfo($this->_path);
		return $this->_pathinfo;
	}
	
	public function setPath($path)
	{
		$this->_path = $path;
	}
}