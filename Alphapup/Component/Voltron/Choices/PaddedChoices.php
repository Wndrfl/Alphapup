<?php
namespace Alphapup\Component\Voltron\Choices;

use Alphapup\Component\Voltron\Choices\BaseChoices;

class PaddedChoices extends BaseChoices
{
	private
		$_padBoth=false,
		$_padLength,
		$_padString,
		$_padType;
		
	public function __construct(array $choices=array(),$padLength,$padString,$padType=STR_PAD_LEFT,$padBoth=false)
	{
		parent::__construct($choices);
		$this->_padBoth = (bool)$padBoth;
		$this->_padLength = $padLength;
		$this->_padString = $padString;
		$this->_padType = $padType;
	}
	
	public function _loadChoices()
	{
		$choices = array();
		foreach($this->_choices as $key => $val) {
			if($this->_padBoth) { $key = str_pad($key,$this->_padLength,$this->_padString,$this->_padType);}
			$choices[$key] = str_pad($val,$this->_padLength,$this->_padString,$this->_padType);
		}
		$this->_choices = $choices;
	}
}