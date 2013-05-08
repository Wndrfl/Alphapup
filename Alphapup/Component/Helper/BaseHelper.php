<?php
namespace Alphapup\Component\Helper;

use Alphapup\Component\View\View;

abstract class BaseHelper implements HelperInterface
{
	private
		$_view;
		
	public function setView(View $view)
	{
		$this->_view = $view;
	}
	
	public function view()
	{
		return $this->_view;
	}
}