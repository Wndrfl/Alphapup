<?php
namespace Alphapup\Component\Voltron\Choices;

use Alphapup\Component\Voltron\Choices\PaddedChoices;

class MonthChoices extends PaddedChoices
{
	private
		$_format;
		
	public function __construct(array $months=array(),$format)
	{
		parent::__construct(array_combine($months,$months),2,'0',STR_PAD_LEFT,false);
		$this->_format = $format;
	}
	
	public function _loadChoices()
	{
		parent::_loadChoices();
		
		foreach($this->_choices as $key => $val) {
			$this->_choices[$key] = date($this->_format,gmmktime(0,0,0,$val,15));
		}
	}
}