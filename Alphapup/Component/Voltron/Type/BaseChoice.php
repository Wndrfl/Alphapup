<?php
namespace Alphapup\Component\Voltron\Type;

use Alphapup\Component\Voltron\Form\Field\BaseField;

abstract class BaseChoice extends BaseField
{
	private
		$_expanded=false,
		$_options = array(),
		$_size,
		$_sort;
		
	public function __construct($name,$label,$options=array())
	{
		parent::__construct($name,$label,$options);
		
		if(isset($options['expanded'])) {
			$this->setExpanded($options['expanded']);
		}
		if(isset($options['multiple'])) {
			$this->setMultiple($options['multiple']);
		}
		if(isset($options['options']) && is_array($options['options']) && $options) {
			$this->setOptions($options['options']);
		}
		if(isset($options['size']) && is_array($options['size'])) {
			$this->setSize($options['size']);
		}
		if(isset($options['sort'])) {
			$this->setSort($options['sort']);
		}
	}
	
	public function expanded()
	{
		return $this->_expanded;
	}
	
	public function option($key)
	{
		if(!isset($this->_options[$key])) {
			// DO EXCEPTION
			return false;
		}
		return $this->_options[$key];
	}
	
	public function options()
	{
		return $this->_options;
	}
	
	public function render()
	{
		$this->sortOptions();
		
		switch($this->type()) {
			case 'select':
				$rendered = $this->renderSelect();
				break;
			case 'checkbox':
				$rendered = $this->renderCheckboxes();
				break;
			case 'radio':
				$rendered = $this->renderRadios();
				break;
		}
		return $rendered;
	}

	public function renderCheckboxes()
	{
		$checkboxes = array();
		foreach($this->_options as $value => $label) {
			$checkbox = '<input type="checkbox" name="'.$this->name().'" value="'.$value.'"';
			if($value == $this->value()) {
				$checkbox .= ' checked="checked"';
			}
			$checkbox .= ' />';
			$checkboxes[] = $checkbox;
		}
		return $checkboxes;
	}

	public function renderRadios()
	{
		$radios = array();
		foreach($this->_options as $value => $label) {
			$radio = '<input type="radio" name="'.$this->name().'" value="'.$value.'"';
			if($value == $this->value()) {
				$radio .= ' checked="checked"';
			}
			$radio .= ' />';
			$radios[] = $radio;
		}
		return $radios;
	}
	
	public function renderSelect()
	{
		$html =  '<select name="'.$this->name().'"';
		if($this->multiple()) {
			$html .= ' multiple="multiple"';
		}
		if($size = $this->size()) {
			$html .= ' size="'.$size.'"';
		}
		if($this->disabled()) {
			$html .= ' disabled="disabled"';
		}
		$html .= '>';
		foreach($this->_options as $value => $label) {
			$html .= '<option value="'.$value.'"';
			if($value == $this->value()) {
				$html .= ' selected="selected"';
			}
			$html .= '>'.$label.'</option>';
		}
		$html .= '</select>';
		return $html;
	}
	
	public function setExpanded($toggle=true)
	{
		$this->_expanded = (bool)$toggle;
	}
	
	public function setSize($size)
	{
		$this->_size = intval($size);
	}
	
	public function size()
	{
		return (!empty($this->_size)) ? $this->_size : false;
	}
	
	public function setOption($value,$label)
	{
		$this->_options[$value] = $label;
	}
	
	public function setOptions($options=array())
	{
		foreach($options as $value => $label) {
			$this->setOption($value,$label);
		}
	}
	
	public function setSort($sort)
	{
		$this->_sort = $sort;
	}
	
	public function sortOptions()
	{
		$sort = (isset($this->_sort)) ? $this->_sort : (isset($this->_defaultSort) ? $this->_defaultSort : false);
		if(!$sort) {
			return;
		}
		switch($sort) {
			case 'ascending':
				asort($this->_options);
				break;
			case 'ascendingValues':
				ksort($this->_options);
				break;
			case 'descending':
				arsort($this->_options);
				break;
			case 'descendingValues':
				krsort($this->_options);
				break;
		}
	}
	
	public function type()
	{
		if($this->expanded()) {
			if($this->multiple()) {
				return 'checkbox';
			}else{
				return 'radio';
			}
		}
		return 'select';
	}
}