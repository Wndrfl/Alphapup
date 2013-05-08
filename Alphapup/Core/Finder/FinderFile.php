<?php
namespace Alphapup\Core\Finder;

class FinderFile
{
	private $_contents;
	private $_path;
	
	public function __construct($path,$contents='') {
		$this->setPath($path);
		$this->setContents($contents);
	}
	
	public function contents() {
		return (!empty($this->_contents)) ? $this->_contents : '';
	}
	
	public function extension() {
		$extension = pathinfo($this->path(), PATHINFO_EXTENSION);
		if(!$extension || $extension === '') {
			return '';
		}
		return $extension;
	}
	
	public function path() {
		return (!empty($this->_path)) ? $this->_path : '';
	}
	
	public function setContents($contents) {
		$this->_contents = $contents;
	}
	
	public function setPath($path) {
		$this->_path = $path;
	}
}