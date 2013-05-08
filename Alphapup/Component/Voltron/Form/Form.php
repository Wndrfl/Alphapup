<?php
namespace Alphapup\Component\Voltron\Form;

use Alphapup\Component\Voltron\Form\Field\Date;
use Alphapup\Component\Voltron\Form\Field\Day;
use Alphapup\Component\Voltron\Form\Field\Email;
use Alphapup\Component\Voltron\Form\Field\FieldInterface;
use Alphapup\Component\Voltron\Form\Field\Month;
use Alphapup\Component\Voltron\Form\Field\Password;
use Alphapup\Component\Voltron\Form\Field\Select;
use Alphapup\Component\Voltron\Form\Field\Submit;
use Alphapup\Component\Voltron\Form\Field\Text;
use Alphapup\Component\Voltron\Form\Field\Year;

class Form
{
	private
		$_children=array(),
		$_name;
		
	public function __construct($name)
	{
		$this->setName($name);
	}
	
	public function add(Form $child)
	{
		$this->_children[$child->name()] = $child;
	}
	
	public function setName($name)
	{
		$this->_name = $name;
	}
}