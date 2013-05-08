<?php
namespace Alphapup\Component\Asset;

use Alphapup\Component\Asset\FileAsset;
use Alphapup\Component\Filter\FilterInterface;

class AssetGroup
{
	private
		$_files = array(),
		$_fileAssets = array(),
		$_filters = array(),
		$_type,
		$_url;
		
	public function __construct($group=array())
	{
		foreach($group['files'] as $file) {
			$this->_files[] = $file;
			$this->setFileAsset(new FileAsset($file));
		}
		if(isset($group['filters']) && is_array($group['filters'])) {
			foreach($group['filters'] as $filter) {
				$this->setFilter($filter);
			}
		}
		if(isset($group['type'])) {
			$this->setType($group['type']);
		}
		if(isset($group['url'])) {
			$this->setUrl($group['url']);
		}
	}
	
	public function correctRelativeUrls(FileAsset $asset)
	{
		$content = preg_replace('|\(../|','('.$asset->dir().'/../',$asset->content());
		return $content;
	}
	
	public function dump()
	{
		$string = '';
		foreach($this->_fileAssets as $asset) {
			$content = $this->correctRelativeUrls($asset);
			foreach($this->_filters as $filter) {
				$content = $filter->filter($content);
			}
			$string .= $content;
		}
		return $string;
	}
	
	public function files()
	{
		return $this->_files;
	}
	
	public function filters()
	{
		return $this->_filters;
	}
	
	public function filterString($string)
	{
		foreach($this->_filters as $filter) {
			$string = $filter->filter($asset);
		}
	}
	
	public function setFileAsset(FileAsset $fileAsset)
	{
		$this->_fileAssets[] = $fileAsset;
	}
	
	public function setFileAssets($fileAssets=array())
	{
		foreach($fileAssets as $fileAsset) {
			$this->setFileAsset($fileAsset);
		}
	}
	
	public function setFilter(FilterInterface $filter)
	{
		$this->_filters[] = $filter;
	}
	
	public function setFilters($filters=array())
	{
		foreach($filters as $filter) {
			$this->setFilter($filter);
		}
	}
	
	public function setType($type)
	{
		$this->_type = $type;
	}
	
	public function setUrl($url)
	{
		$this->_url = $url;
	}
	
	public function type()
	{
		return $this->_type;
	}
	
	public function url()
	{
		return $this->_url;
	}
}