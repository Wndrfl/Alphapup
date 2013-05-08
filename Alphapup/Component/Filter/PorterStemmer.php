<?php
namespace Alphapup\Component\Filter;

use Alphapup\Component\Filter\Interface;

/**
* AlphaPup port of Richard's original PHP5 port
* Copyright (c) 2005 Richard Heyes (http://www.phpguru.org/)
* All rights reserved.
* This script is free software.
*/
class PorterStemmer implements FilterInterface
{
	//Regex for matching a consonant
	private $_regexConsonant  = '(?:[bcdfghjklmnpqrstvwxz]|(?<=[aeiou])y|^y)';

	//Regex for matching a vowel
	private $_regexVowel = '(?:[aeiou]|(?<![aeiou])y)';

	public function filter($value)
	{
		if(strlen($value) <= 2) {
			return $value;
		}

		$value = $this->step1ab($value);
		$value = $this->step1c($value);
		$value = $this->step2($value);
		$value = $this->step3($value);
		$value = $this->step4($value);
		$value = $this->step5($value);

		return $value;
	}
	
	public function name()
	{
		return 'porterStemmer';
	}

	private function step1ab($word)
	{
		// Part a
		if(substr($word,-1) == 's') {
			$this->replace($word, 'sses', 'ss')
				|| $this->replace($word, 'ies', 'i')
				|| $this->replace($word, 'ss', 'ss')
				|| $this->replace($word, 's', '');
		}

		// Part b
		if(substr($word,-2,1) != 'e' || !$this->replace($word,'eed','ee',0)) { // First rule
			$v = $this->_regexVowel;

			// ing and ed
			if(preg_match("#$v+#",substr($word,0,-3)) && $this->replace($word,'ing','')
			|| preg_match("#$v+#",substr($word,0,-2)) && $this->replace($word,'ed','')) { // Note use of && and ||, for precedence reasons

				// If one of above two test successful
				if(!$this->replace($word,'at','ate') && !$this->replace($word,'bl','ble') && !$this->replace($word,'iz','ize')) {

					// Double consonant ending
					if($this->doubleConsonant($word) && substr($word,-2) != 'll' && substr($word,-2) != 'ss' && substr($word,-2) != 'zz') {
	   					$word = substr($word, 0, -1);
					}elseif($this->m($word) == 1 && $this->cvc($word)) {
	   					$word .= 'e';
					}
				}
			}
		}

		return $word;
	}

	private function step1c($word)
	{
		$v = $this->_regexVowel;

		if(substr($word, -1) == 'y' && preg_match("#$v+#", substr($word, 0, -1))) {
			$this->replace($word,'y','i');
		}

		return $word;
	}

	private function step2($word)
	{
		switch(substr($word,-2,1)) {
			case 'a':
			$this->replace($word,'ational','ate',0) || $this->replace($word,'tional','tion',0);
			break;

			case 'c':
			$this->replace($word,'enci','ence',0) || $this->replace($word,'anci','ance',0);
			break;

			case 'e':
			$this->replace($word,'izer','ize',0);
			break;

			case 'g':
			$this->replace($word,'logi','log',0);
			break;

			case 'l':
			$this->replace($word,'entli','ent',0)
				|| $this->replace($word,'ousli','ous',0)
				|| $this->replace($word,'alli','al',0)
				|| $this->replace($word,'bli','ble',0)
				|| $this->replace($word,'eli','e',0);
			break;

			case 'o':
			$this->replace($word,'ization','ize',0)
				|| $this->replace($word,'ation','ate',0)
				|| $this->replace($word,'ator','ate',0);
			break;

			case 's':
			$this->replace($word,'iveness','ive',0)
				|| $this->replace($word,'fulness','ful',0)
				|| $this->replace($word,'ousness','ous',0)
				|| $this->replace($word,'alism','al',0);
			break;

			case 't':
			$this->replace($word,'biliti','ble',0)
				|| $this->replace($word,'aliti','al',0)
				|| $this->replace($word,'iviti','ive',0);
			break;
		}

		return $word;
	}

	private function step3($word)
	{
		switch(substr($word,-2,1)) {
			case 'a':
			$this->replace($word,'ical','ic',0);
			break;

			case 's':
			$this->replace($word,'ness','',0);
			break;

			case 't':
			$this->replace($word,'icate','ic',0) || $this->replace($word, 'iciti', 'ic', 0);
			break;

			case 'u':
			$this->replace($word,'ful','',0);
			break;

			case 'v':
			$this->replace($word, 'ative', '', 0);
			break;

			case 'z':
			$this->replace($word, 'alize', 'al', 0);
			break;
		}

		return $word;
	}

	private function step4($word)
	{
		switch (substr($word,-2,1)) {
			case 'a':
			$this->replace($word,'al','',1);
			break;

			case 'c':
			$this->replace($word,'ance','',1)
				|| $this->replace($word,'ence','',1);
			break;

			case 'e':
			$this->replace($word,'er','',1);
			break;

			case 'i':
			$this->replace($word,'ic','',1);
			break;

			case 'l':
			$this->replace($word,'able','',1)
				|| $this->replace($word, 'ible', '', 1);
			break;

			case 'n':
			$this->replace($word,'ant','',1)
				|| $this->replace($word,'ement','',1)
				|| $this->replace($word,'ment','',1)
				|| $this->replace($word,'ent','',1);
			break;

			case 'o':
			if(substr($word, -4) == 'tion' || substr($word, -4) == 'sion') {
				$this->replace($word,'ion','',1);
			}else{
				$this->replace($word,'ou','',1);
			}
			break;

			case 's':
			$this->replace($word,'ism','',1);
			break;

			case 't':
			$this->replace($word,'ate','',1)
				|| $this->replace($word,'iti','',1);
			break;

			case 'u':
			$this->replace($word,'ous','',1);
			break;

			case 'v':
			$this->replace($word,'ive','',1);
			break;

			case 'z':
			$this->replace($word,'ize','',1);
			break;
		}

		return $word;
	}

	private function step5($word)
	{
		// Part a
		if(substr($word,-1) == 'e') {
			if($this->m(substr($word,0,-1)) > 1) {
				$this->replace($word,'e','');

			}elseif($this->m(substr($word,0,-1)) == 1) {

				if(!$this->cvc(substr($word,0,-1))) {
					$this->replace($word,'e','');
				}
			}
		}

		// Part b
		if($this->m($word) > 1 && $this->doubleConsonant($word) && substr($word, -1) == 'l') {
			$word = substr($word, 0, -1);
		}

		return $word;
	}


	/**
	* Replaces the first string with the second, at the end of the string. If third
	* arg is given, then the preceding string must match that m count at least.
	*/
	private function replace(&$str,$check,$repl,$m = null)
	{
		$len = 0 - strlen($check);

		if(substr($str,$len) == $check) {
			$substr = substr($str, 0, $len);
			if(is_null($m) || $this->m($substr) > $m) {
				$str = $substr . $repl;
			}

			return true;
		}

		return false;
	}


	/**
	* m() measures the number of consonant sequences in $str. if c is
	* a consonant sequence and v a vowel sequence, and <..> indicates arbitrary
	* presence,
	*
	* <c><v>       gives 0
	* <c>vc<v>     gives 1
	* <c>vcvc<v>   gives 2
	* <c>vcvcvc<v> gives 3
	*/
	private function m($str)
	{
		$c = $this->_regexConsonant ;
		$v = $this->_regexVowel;

		$str = preg_replace("#^$c+#",'',$str);
		$str = preg_replace("#$v+$#",'',$str);

		preg_match_all("#($v+$c+)#",$str,$matches);

		return count($matches[1]);
	}


	/**
	* Returns true/false as to whether the given string contains two
	* of the same consonant next to each other at the end of the string.
	*
	* @param  string $str String to check
	* @return bool        Result
	*/
	private function doubleConsonant($str)
	{
		$c = $this->_regexConsonant ;
		return preg_match("#$c{2}$#", $str, $matches) && $matches[0]{0} == $matches[0]{1};
	}


	/**
	* Checks for ending CVC sequence where second C is not W, X or Y
*/
	private function cvc($str)
	{
		$c = $this->_regexConsonant ;
		$v = $this->_regexVowel;

		return preg_match("#($c$v$c)$#", $str, $matches)
			&& strlen($matches[1]) == 3
			&& $matches[1]{2} != 'w'
			&& $matches[1]{2} != 'x'
			&& $matches[1]{2} != 'y';
		}
	}
?>