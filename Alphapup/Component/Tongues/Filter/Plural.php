<?php
namespace Alphapup\Component\Tongues\Filter;

use Alphapup\Component\Tongues\Filter\BaseFilter;
use Alphapup\Component\Tongues\TongueString;

class Plural extends BaseFilter
{
	private
		$_dontChange = array(
			'data',
			'media'
		),
		$_latinGreek = array(
			'appendix' => 'appendixes',
			'cactus' => 'cacti',
			'crisis' => 'crises',
			'criterion' => 'criteria',
			'focus' => 'foci',
			'fungus' => 'fungi',
			'index' => 'indices',
			'nucleus' => 'nuclei',
			'phenomenon' => 'phenomena',
			'syllabus' => 'syllabi',
			'thesis' => 'theses',
		),
		$_mutate = array(
			'alumni' => 'alumnus',
			'barracks' => 'barracks',
			'child' => 'children',
			'deer' => 'deer',
			'goose' => 'geese',
			'man' => 'men',
			'mouse' => 'mice',
			'person' => 'people',
			'woman' => 'women',
			
		),
		$_name = 'plural',
		$_oes = array(
			'echo',
			'embargo',
			'hero',
			'potato',
			'tomato',
			'torpedo',
			'veto',
		),
		$_specialF = array(
			'goof',
			'roof',
			'spoof',
			'wharf'
		);
	
	public function filter(TongueString $string,FilterMatch $match)
	{
		if($count = $match->attribute('count')) {
			if(is_numeric($count) && $count == 1) {
				return;
			}
			if(!is_numeric($count) || is_null($count) || $count == false) {
				if($default = $match->attribute('default') && $default == false) {
					return;
				}
			}
		}
		
		$content = $match->content();
		$ct = $match->closingTagIndex();
		$m1 = strtolower($string->charAt($ct-1));
		$m2 = strtolower($string->charAt($ct-2));
		$m3 = strtolower($string->charAt($ct-3));
		$last2 = $m2.$m1;
		$last3 = $m3.$m2.$m1;
		
		// If possessive
		if($m1 == '\'' || $last2 == '\'s') {
			$l = ($m1 == '\'') ? 1 : 2;
			$content = substr($content,0,$content-$l);
			$m1 = $content[strlen($content)-1];
			$m2 = $content[strlen($content)-2];
			$m3 = $content[strlen($content)-3];
			$last2 = $m2.$m1;
			$last3 = $m3.$m2.$m1;
			$p = '\'';
		}else{
			$p = null;
		}

		$words = explode(' ',$content);
		$lastWord = $words[count($words)-1];
		$lastWordIsUpper = $string->isUppercase($lastWord[0]);
		
		// already plural
		if($last3 == 'ies' || $last3 == 'oes') {
			return;
		}
			
		$replace = $content.'s'.$p;
		
		// Special case : don't change
		if(in_array($lastWord,$this->_dontChange)) {
			$replace = $content.$p;
			
		// Special case : mutations
		}elseif(isset($this->_mutate[$lastWord])) {
			$word = ($lastWordIsUpper) ? ucfirst($this->_mutate[$lastWord]) : $this->_mutate[$lastWord];
			if($p) {
				$word .= ($word[strlen($word)-1] == 's') ? '\'' : '\'s';
			}
			$replace = substr($content,0,strlen($content)-strlen($lastWord)).$word;
			
		// Special case : latin / greek
		}elseif(isset($this->_latinGreek[$lastWord])) {
			$word = ($lastWordIsUpper) ? ucfirst($this->_latinGreek[$lastWord]) : $this->_latinGreek[$lastWord];
			if($p) {
				$word .= ($word[strlen($word)-1] == 's') ? '\'' : '\'s';
			}
			$replace = substr($content,0,strlen($content)-strlen($lastWord)).$word;
		
		// Proper noun
		}elseif($lastWordIsUpper) {
			$replace = $content.'s'.$p;
			
		// If only one letter
		}elseif(strlen($lastWord) == 1) {
			$replace = $content.'\'s';
			
		
		// Nouns ending in s, z, ch, sh, and x
		}elseif($m1 == 's' || $m1 == 'z' || $m1 == 'x' || $last2 == 'ch' || $last2 == 'sh') {
			$replace = $content.'es'.$p;
		
		// Nouns ending in o
		}elseif($m1 == 'o') {
			
			if(in_array($lastWord,$this->_oes)) {
				$replace = $content.'es'.$p;
			}else{
				$replace = $content.'s'.$p;
			}
		
		// Ends with consonant and y
		}elseif($string->isConsonant($m2) && $m1 == 'y') {
			$replace = substr($content,0,strlen($content)-1).'ies'.$p;
		
		// Ends in f or fe	
		}elseif($m1 == 'f' || $last2 == 'fe') {
			
			if(in_array($lastWord,$this->_specialF)) {
				$replace = $content.'s'.$p;
			}else{
				$l = ($m1 == 'f') ? 1 : 2;
				$replace = substr($content,0,strlen($content)-$l).'ves'.$p;
			}
		}
		
		$this->setReplacement($match,$string,$replace);
	}
	
	public function name()
	{
		return $this->_name;
	}
}