<?php
namespace Alphapup\Component\Tongues\Filter;

class FilterMatch
{
	private
		$_attributes,
		$_match = array();
	
	public function __construct($match=array())
	{
		$this->_match = $match;
	}
	
	public function attribute($attr)
	{
		$attributes = $this->attributes();
		return (isset($attributes[$attr])) ? $attributes[$attr] : false;
	}
	
	public function attributes()
	{
		if(!empty($this->_attributes)) {
			return $this->_attributes;
		}
		if($this->_match[2][0] == '') {
			$this->_attributes = array();
			return $this->_attributes;
		}
		$parts = explode(' ',trim($this->_match[2][0],' '));
		$attr = array();
		foreach($parts as $part) {
			if($part == ' ') {
				continue;
			}
			$kv = explode('=',str_replace(array('\'','"'),'',$part));
			if(!isset($kv[1])) {
				$attr[$kv[0]] = true;
			}else{
				$attr[$kv[0]] = $kv[1];
			}
		}
		$this->_attributes = $attr;
		return $this->_attributes;
	}
	
	public function closingTagIndex($num=0)
	{
		return $this->_match[4][1]+$num;
	}
	
	public function content()
	{
		return $this->_match[3][0];
	}
	
	public function fullMatchIndex($num=0)
	{
		return $this->_match[0][1]+$num;
	}
	
	public function fullMatchLength($num=0)
	{
		return strlen($this->_match[0][0])+$num;
	}
	
	public function openingTagContent()
	{
		return $this->_match[1][0];
	}
}