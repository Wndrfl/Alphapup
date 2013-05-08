<?php
namespace Alphapup\Component\Asset;

use Alphapup\Component\Asset\AssetGroup;
use Alphapup\Component\Asset\Exception\AssetGroupDoesNotExistException;

class AssetManager
{
	private
		$_assetGroups = array();
		
	public function __construct($groups=array())
	{
		$this->setAssetGroups($groups);
	}
	
	public function group($group)
	{
		if(isset($this->_assetGroups[$group])) {
			$group = $this->_assetGroups[$group];
			return $group;
		}
		throw new AssetGroupDoesNotExistException($group);
	}
	
	public function render($group)
	{
		$group = $this->group($group);
		$files = $group->files();
		$filters = $group->filters();
		
		$string = '';
		foreach($files as $file) {
			if(strpos($file,'&') === 0) {
				$reference = substr($file,1);
				$content = $this->render($reference);
				
			}else{

				if(!file_exists($file) || !$fh = @fopen($file,'r')) {
					throw new CantOpenFileException($this->_path);
				}
				
				$size = filesize($file);
				$content = $size ? fread($fh,$size) : '';
				fclose($fh);
				
			}
			foreach($filters as $filter) {
				$content = $filter->filter($content);
			}
			$string .= $content;
		}
		return $string;
	}
	
	public function setAssetGroup($name,$group)
	{
		$assetGroup = new AssetGroup($group);
		$this->_assetGroups[$name] = $assetGroup;
	}
	
	public function setAssetGroups($groups=array())
	{
		foreach($groups as $name => $group) {
			$this->setAssetGroup($name,$group);
		}
	}
}