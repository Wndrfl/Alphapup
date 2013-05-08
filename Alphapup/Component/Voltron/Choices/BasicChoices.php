<?php
namespace Alphapup\Component\Voltron\Choices;

use Alphapup\Component\Voltron\Choices\BaseChoices;

class BasicChoices extends BaseChoices
{
	public function __construct(array $choices=array())
	{
		parent::__construct($choices);
	}
	
	public function _loadChoices()
	{
		return $this->_choices;
	}
}