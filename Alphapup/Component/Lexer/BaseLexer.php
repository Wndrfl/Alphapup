<?php
namespace Alphapup\Component\Lexer;

abstract class BaseLexer implements LexerInterface
{	
	private
		$_next = 1,
		$_peek = 0,
		$_pointer = 0,
		$_previous = -1,
		$_tokens = array();
		
	public function aheadOffset($ahead)
	{
		$ahead = intval($ahead);
		if(isset($this->_tokens[$this->_pointer+$ahead])) {
			return $this->_tokens[$this->_pointer+$ahead]['offset'];
		}
		return null;
	}
	
	public function aheadType($ahead)
	{
		$ahead = intval($ahead);
		if(isset($this->_tokens[$this->_pointer+$ahead])) {
			return $this->_tokens[$this->_pointer+$ahead]['type'];
		}
		return null;
	}
	
	public function aheadValue($ahead)
	{
		$ahead = intval($ahead);
		if(isset($this->_tokens[$this->_pointer+$ahead])) {
			return $this->_tokens[$this->_pointer+$ahead]['value'];
		}
		return null;
	}
	
	public function current()
	{
		if(!isset($this->_tokens[$this->_pointer])) {
			return null;
		}
		return $this->_tokens[$this->_pointer];
	}
	
	public function currentOffset()
	{
		if(!isset($this->_tokens[$this->_pointer])) {
			return null;
		}
		return $this->_tokens[$this->_pointer]['offset'];
	}
	
	public function currentType()
	{
		if(!isset($this->_tokens[$this->_pointer])) {
			return null;
		}
		return $this->_tokens[$this->_pointer]['type'];
	}
	
	public function currentValue()
	{
		if(!isset($this->_tokens[$this->_pointer])) {
			return null;
		}
		return $this->_tokens[$this->_pointer]['value'];
	}
	
	public function isNextType($type)
	{
		return ($this->nextType() == $type) ? true : false;
	}
	
	public function goUntil($type)
	{
		while(isset($this->_tokens[$this->_pointer])) {
			if($this->_tokens[$this->_pointer]['type'] == $type) {
				return $this->_tokens[$this->_pointer];
			}
			$this->onToNext();
		}
		return false;
	}
	
	public function matchNext($token)
	{
		if($this->nextType() != $token) {
			// DO EXCEPTION
			die('cql syntax error, expecting '.$token);
			return false;
		}
		$this->onToNext();
	}
	
	public function nextOffset()
	{
		if(isset($this->_tokens[$this->_next])) {
			return $this->_tokens[$this->_next]['offset'];
		}
		return null;
	}
	
	public function nextType()
	{
		if(isset($this->_tokens[$this->_next])) {
			return $this->_tokens[$this->_next]['type'];
		}
		return null;
	}
	
	public function nextTypeIs($type)
	{
		$nextType = $this->nextType();
		if(is_array($type)) {
			return (in_array($nextType,$type)) ? true : false;
		}else{
			return $nextType == $type;
		}
	}
	
	public function nextValue()
	{
		if(isset($this->_tokens[$this->_next])) {
			return $this->_tokens[$this->_next]['value'];
		}
		return null;
	}
	
	public function onToNext()
	{
		$this->_pointer++;
		$this->_next++;
		return $this;
	}
	
	public function onToNextPeek()
	{
		$this->_peek++;
		return $this;
	}
	
	public function parseTokens($input)
	{
		$this->reset();
		
		$regex = '/('.implode(')|(',$this->tokenPatterns()).')|'.implode('|',$this->ignorePatterns()).'/i';
		
		$flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
        $matches = preg_split($regex, $input, -1, $flags);

		foreach($matches as $match) {
			$type = $this->typeFor($match[0]);
			
			$this->_tokens[] = array(
				'value' => $match[0],
				'type' => $type,
				'offset' => $match[1]
			);
		}
		
		return $this;
	}
	
	public function peekOffset()
	{
		if(isset($this->_tokens[$this->_peek])) {
			return $this->_tokens[$this->_peek]['offset'];
		}
		return null;
	}
	
	public function peekTo($tokenNumber)
	{
		$this->_peek = $tokenNumber;
		return $this;
	}
	
	public function peekToNextType($type)
	{
		$this->_peek = $this->_pointer;
		$end = count($this->_tokens)-1; 
		while($this->_tokens[$this->_pointer]['type'] != $type && $this->_peek <= $end) {
			if($this->_tokens[$this->_pointer]['type'] == $type) {
				return $this->_tokens[$this->_pointer];
			}
			$this->_peek++;
		}
		return false;
		
	}
	
	public function peekType()
	{
		if(isset($this->_tokens[$this->_peek])) {
			return $this->_tokens[$this->_peek]['type'];
		}
		return null;
	}
	
	public function peekValue()
	{
		if(isset($this->_tokens[$this->_peek])) {
			return $this->_tokens[$this->_peek]['value'];
		}
		return null;
	}
	
	public function pointer()
	{
		return $this->_pointer;
	}
	
	public function previousOffset()
	{
		if(!isset($this->_tokens[$this->_previous])) {
			return null;
		}
		return $this->_tokens[$this->_previous]['offset'];
	}
	
	public function previousType()
	{	
		if(!isset($this->_tokens[$this->_previous])) {
			return null;
		}
		return $this->_tokens[$this->_previous]['type'];
	}
	
	public function previousValue()
	{
		if(!isset($this->_tokens[$this->_previous])) {
			return null;
		}
		return $this->_tokens[$this->_previous]['value'];
	}
	
	public function reset()
	{
		$this->resetPointer();
		$this->_tokens = array();
	}
	
	public function resetPointer($index=0)
	{
		$this->_pointer = $index;
		$this->_next = ($this->_pointer+1);
	}
}