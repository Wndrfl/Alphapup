<?php
namespace Alphapup\Component\Filter\Minify;

use Alphapup\Component\Filter\FilterInterface;

abstract class MinifierAbstract implements FilterInterface
{
	
	private $_currLineLength = 0;
	private $_escapeNext = false;
	private $_inString = false;
	private $_lineLength = 1000;
	private $_output = '';
	private $_pointer = 0;
	private $_raw;
	private $_stringDelimiter;
	
	public function append($content)
	{
		$this->_output = $this->_output.$content;
		$this->_currLineLength += strlen($content);
		$breakable = array('}',';');
		if($this->_lineLength > 0 && $this->_currLineLength > $this->_lineLength) {
			if(in_array($content,$breakable)) {
				$this->_output .= "\n";
				$this->_currLineLength = 0;
			}
		}
	}
	
	public function cacheOutput($ttl=null)
	{
		$this->_setCache($this->output(),$ttl);
	}
	
	public function characterAt($num)
	{
		return (isset($this->_raw[$num])) ? $this->_raw[$num] : '';
	}
	
	public function clearEscape()
	{
		$this->_escapeNext = false;
	}
	
	public function current()
	{
		return (isset($this->_raw[$this->pointer()])) ? $this->_raw[$this->pointer()] : false;
	}
	
	public function currentIsSpace()
	{
		return (ctype_space($this->current()));
	}
	
	public function currentToOutput()
	{
		$this->append($this->current());
	}
	
	public function escapeCurrent()
	{
		return ($this->_escapeNext == true) ? true : false;
	}
	
	public function escapeNext()
	{
		$this->_escapeNext = true;
	}
	
	public function inString($toggle=null)
	{
		if(!is_null($toggle)) {
			$this->_inString = $toggle;
			return;
		}
		return (bool) $this->_inString;
	}
	
	public function isAlphanumeric($str)
	{
		return (ord($str) > 126 || $str === '\\' || preg_match('/^[\w\$]$/',$str) === 1);
	}
	
	public function isEscape($str)
	{
		return ($str == '\\') ? true : false;
	}
	
	public function filter($value)
	{	
		$this->_raw = $value;
		$this->reset();
		$this->process();
		return $this->output();
	}
	
	public function next()
	{
		return (isset($this->_raw[$this->pointer(1)])) ? $this->_raw[$this->pointer(1)] : false;
	}
	
	public function onToNext()
	{
		$this->skip(1);
	}
	
	public function output()
	{
		return $this->_output;
	}
	
	public function pointer($num=0)
	{
		return $this->_pointer+$num;
	}
	
	public function previous()
	{
		return (isset($this->_raw[$this->pointer(-1)])) ? $this->_raw[$this->pointer(-1)] : false;
	}
	
	public function processBetween($prev,$next)
	{
		if(is_array($prev) && !in_array($this->previous(),$prev)) {
			return false;
		}elseif($prev == 'alpha' && !$this->isAlphanumeric($this->previous())) {
			return false;
		}elseif(!is_array($prev) && $prev != 'alpha' && $this->previous() != $prev) {
			return false;
		}
		
		if(is_array($next) && !in_array($this->next(),$next)) {
			return false;
		}elseif($next == 'alpha' && !$this->isAlphanumeric($this->next())) {
			return false;
		}elseif(!is_array($next) && $next != 'alpha' && $this->next() != $next) {
			return false;
		}
		$this->recordAndOnward();
		return true;
	}
	
	public function processEscape()
	{
		if($this->current() == '\\') {
			if($this->inString()) {
				$this->recordAndOnward();
				$this->escapeNext();
				return true;
			}
		}
		return false;
	}
	
	public function processHexidecimal()
	{
		$hexis = array('a','b','c','d','e','f','1','2','3','4','5','6','7','8','9');
		if($this->current() == '#' && in_array(strtolower($this->next()),$hexis)) {
			$hex = '';
			for($i=$this->pointer(1);$i<$this->pointer(7);$i++) {
				$hex .= $this->characterAt($i);
			}
			if(ctype_xdigit($hex) && !$this->isAlphanumeric($this->characterAt($this->pointer(7)))) {
				$parts = str_split($hex,3);
				if($parts[0] == $parts[1]) {
					$this->append('#'.$parts[0]);
					$this->skip(7);
					return true;
				}
			}
		}
		return false;
	}
	
	public function processInString()
	{
		if($this->inString()) {
			$this->recordAndOnward();
			return true;
		}
		return false;
	}
	
	public function processMultiLineComment()
	{
		if($this->current() == '/' && $this->next() == '*') {
			$comment = strstr(substr($this->raw(),$this->pointer()),'*/',TRUE);
			$skip = strlen($comment)+2;
			$this->skip($skip);
			return true;
		}
		return false;
	}
	
	public function processQuote()
	{
		if($this->current() == '"' || $this->current() == '\'') {
			if($this->inString()) {
				
				// if this is the end of the string
				if($this->current() == $this->stringDelimiter() && !$this->escapeCurrent()) {
					$this->inString(false);
				}
				
			}else{
				$this->stringDelimiter($this->current());
				$this->inString(true);
			}
			$this->recordAndOnward();
			return true;
		}
		return false;
	}
	
	public function processRegex()
	{
		if($this->next() == '/') {
			$regex = true;
			if($this->pointer() > 1) {
			
				// Pattern should be preceded by parenthesis,
				// colon or assignment operator
				$offset = $this->pointer();
				while($offset > 0) {
					$offset--;
				
					// if we found a start of the regex, let's see if we can find the end
					$offset_char = $this->characterAt($offset);
					if($offset_char == '(' || $offset_char == ':' || $offset_char == '=') {
						while($this->pointer() < $this->rawLength()) {
							$str = strstr(substr($this->raw(),$this->pointer(1)),'/',true);
							if(!strlen($str) && $this->characterAt($this->pointer(-1)) != '/' || strpos($str,"\n") !== false) {
								$regex=false;
								break;
							}
							$this->append('/'.$str);
							$this->skip(strlen($str)+1);
							if($this->characterAt($this->pointer(-1)) != '\\' || $this->characterAt($this->pointer(-2)) == '\\') {
								$this->append('/');
								$this->onToNext();
								break;
							}
						}
						break;
					}
				
					// if we're still rewinding and not proven regex
					if($regex && $offset < 1) {
						$regex = false;
						break;
					}
				}
				if($regex) {
					return true;
				}
			}
		}
		return false;
	}
	
	public function processSingleLineComment()
	{
		if($this->current() == '/' && $this->next() == '/') {
			$comment = strstr(substr($this->raw(),$this->pointer()),"\n",TRUE);
			$skip = strlen($comment)+2;
			$this->skip($skip);
			return true;
		}
		return false;
	}
	
	public function processZeroPixels()
	{
		if($this->current() == '0' && $this->characterAt($this->pointer(1)) == 'p' && $this->characterAt($this->pointer(2)) == 'x') {
			if(!$this->isAlphanumeric($this->characterAt($this->pointer(3))) && !$this->isAlphanumeric($this->previous())) {
				$this->append('0');
				$this->skip(3);
				return true;
			}
		}
		return false;
	}
	
	public function raw()
	{
		return $this->_raw;
	}
	
	public function rawLength()
	{
		return strlen($this->_raw);
	}
	
	public function recordAndOnward()
	{
		$this->currentToOutput();
		$this->skip(1);
	}
	
	public function reset()
	{
		$this->_output = '';
		$this->clearEscape();
		$this->inString(false);
		$this->setCurrLineLength(0);
		$this->setPointer(0);
	}
	
	public function setCurrLineLength($length)
	{
		$this->_currLineLength = $length;
	}

	public function setLineLength($length)
	{
		$this->_lineLength = $length;
	}
	
	public function setPointer($index)
	{
		$this->_pointer = $index;
	}
	
	public function setupRaw($raw)
	{
		if($this->rawLength() > 1) {
			$this->append("\n");
		}
		$this->_raw = $raw;
		$this->reset();
	}
	
	public function skip($skip=1)
	{
		$this->_pointer += $skip;
		$this->clearEscape();
	}
	
	public function stringDelimiter($delimiter=null)
	{
		if(is_null($delimiter)) {
			return (!empty($this->_stringDelimiter)) ? $this->_stringDelimiter : false;
		}
		$this->_stringDelimiter = $delimiter;
	}
}