<?php
namespace Alphapup\Component\Tongues;

class TongueString
{
	private $_consonants = array(
		'b','c','d','f','g','h','j','k','l','m','n','p','q',
		'r','s','t','v','w','x','z','B','C','D','F','G','H',
		'J','K','L','M','N','P','Q','R','S','T','V','W','X',
		'Z'
	);
	private $_vowels = array(
		'a','e','i','o','u','y','A','E','I','O','U','Y'
	);
	private $_escapeNext = false;
	private $_pointer = 0;
	private $_raw;
	private $_replacers = array();
	
	public function __construct($raw)
	{
		$this->_raw = $raw;
	}
	
	public function between($start,$end)
	{
		return substr($this->_raw,$start,$end-$start);
	}

	public function charAt($num)
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
	
	public function deleteAndReplace($length,$replacement)
	{
		$this->deleteBetween($this->pointer(),$this->pointer($length));
		$this->insertAt($this->pointer(),$replacement);
	}
	
	public function deleteBetween($start,$end)
	{
		$this->_raw = substr_replace($this->_raw,'',$start,$end-$start);
	}

	public function escapeCurrent()
	{
		return ($this->_escapeNext == true) ? true : false;
	}

	public function escapeNext()
	{
		$this->_escapeNext = true;
	}
	
	public function insertAt($index,$insert)
	{
		$part1 = substr($this->_raw,0,$index);
		$part2 = substr($this->_raw,$index);
		$new = $part1.$insert.$part2;
		$this->_raw = $new;
	}

	public function isAlphanumeric($str)
	{
		return (ctype_alpha($str)) ? true : (ctype_digit($str) ? true : false);
	}

	public function isConsonant($char)
	{
		return (in_array($char,$this->_consonants)) ? true : false;
	}

	public function isEscape($str)
	{
		return ($str == '\\') ? true : false;
	}
	
	public function isSpace($str)
	{
		return ctype_space($str);
	}
	
	public function isUppercase($str)
	{
		return ctype_upper($str);
	}
	
	public function isVowel($char)
	{
		return (in_array($char,$this->_vowels)) ? true : false;
	}

	public function next()
	{
		return (isset($this->_raw[$this->pointer(1)])) ? $this->_raw[$this->pointer(1)] : false;
	}
	
	public function nextAlpha()
	{
		$offset = 0;
		while($this->pointer($offset) < $this->rawLength()) {
			$testChar = $this->charAt($this->pointer($offset));
			if($this->isAlphanumeric($testChar)) {
				return $testChar;
			}
			$offset++;
		}
	}

	public function pointer($num=0)
	{
		return $this->_pointer+$num;
	}

	public function previous()
	{
		return (isset($this->_raw[$this->pointer(-1)])) ? $this->_raw[$this->pointer(-1)] : false;
	}
	
	public function rawLength()
	{
		return strlen($this->_raw);
	}
	
	public function render()
	{	
		foreach($this->_replacers as $key => $replacer) {
			$totalCut = $replacer[1]-$replacer[0];
			$totalInsert = strlen($replacer[2]);
			$diff = $totalInsert-$totalCut;
			foreach($this->_replacers as $k => $adjust) {
				if($adjust[0] > $replacer[0]) {
					$adjust[0] = $adjust[0]+$diff;
					$adjust[1] = $adjust[1]+$diff;
				}
				$this->_replacers[$k] = $adjust;
			}
		}
		
		foreach($this->_replacers as $replacer) {
			$totalCut = $replacer[1]-$replacer[0];
			$totalInsert = strlen($replacer[2]);
			$diff = $totalInsert-$totalCut;
			$this->setPointer($replacer[0]);
			$this->deleteAndReplace($totalCut,$replacer[2]);
		}
		return $this->text();
	}

	public function setPointer($index)
	{
		$this->_pointer = $index;
	}

	public function setReplacer($start,$stop,$insert)
	{
		$this->_replacers[] = array($start,$stop,$insert);
	}

	public function skip($skip=1)
	{
		$this->_pointer += $skip;
	}
	
	public function text()
	{
		return $this->_raw;
	}
}