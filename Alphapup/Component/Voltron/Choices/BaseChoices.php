<?php
namespace Alphapup\Component\Voltron\Choices;

abstract class BaseChoices
{
	protected
		$_choices = array(),
		$_sort=null;
		
	public function __construct(array $choices=array())
	{
		if($choices) {
			$this->setChoices($choices);
		}
	}
	
	public function choices()
	{
		$this->_loadChoices();
		switch($this->_sort) {
			case 'ascendingByKey':
				ksort($this->_choices);
				break;
			
			case 'ascendingByValue':
				asort($this->_choices);
				break;
				
			case 'descendingByKey':
				krsort($this->_choices);
				break;

			case 'descendingByValue':
				arsort($this->_choices);
				break;
		}
		return $this->_choices;
	}
	
	public function setChoice($key,$value)
	{
		$this->_choices[$key] = $value;
	}
	
	public function setChoices(array $choices=array())
	{
		foreach($choices as $key => $value) {
			$this->setChoice($key,$value);
		}
	}
	
	public function setSort($sort)
	{
		$this->_sort = $sort;
	}
}