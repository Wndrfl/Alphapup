<?php
namespace Alphapup\Component\Filter;

use Alphapup\Component\Filter\Interface;

class HtmlEntities implements FilterInterface
{
	private $_doubleEncode;
	private $_encoding;
	private $_quoteStyle;
	
	public function __construct($options=array()) {
		parent::__construct();
		
		$double_encode = (isset($options['double_encode'])) ? $options['double_encode'] : true;
		$this->setDoubleEncode($double_encode);
		
		$encoding = (isset($options['encoding'])) ? $options['encoding'] : 'UTF-8';
		$this->setEncoding($encoding);
		
		$quote_style = (isset($options['quote_style'])) ? $options['quote_style'] : ENT_COMPAT;
		$this->setQuoteStyle($quote_style);
	}
	
	public function filter($value) {
		$filtered = htmlentities((string) $value,$this->getQuoteStyle(),$this->getEncoding(),$this->getDoubleEncode());
        if(strlen((string) $value) && !strlen($filtered)) {
            if (!function_exists('iconv')) {
				return;
            }
            $enc = $this->getEncoding();
            $value = iconv('',$enc.'//IGNORE',(string) $value);
            $filtered = htmlentities($value, $this->getQuoteStyle(),$enc,$this->getDoubleEncode());
            if(!strlen($filtered)) {
                return;
            }
        }
		return $filtered;
	}
	
	public function getDoubleEncode() {
		return (bool) $this->_doubleEncode;
	}
	
	public function getEncoding() {
		return $this->_encoding;
	}
	
	public function getQuoteStyle() {
		return $this->_quoteStyle;
	}
	
	public function name()
	{
		return 'htmlEntities';
	}
	
	public function setDoubleEncode(Bool $toggle) {
		$this->_doubleEncode = $toggle;
		return $this;
	}
	
	public function setEncoding($encoding) {
		$this->_encoding = $encoding;
		return $this;
	}
	
	public function setQuoteStyle($style) {
		$this->_quoteStyle = $style;
		return $this;
	}
}