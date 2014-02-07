<?php
namespace Alphapup\Component\Helper;

use Alphapup\Component\Helper\BaseHelper;
use Alphapup\Component\Voltron\VoltronView;

class VoltronHelper extends BaseHelper
{
	private function _renderType($type,VoltronView $voltron)
	{
		$view = $this->view()->view('Alphapup','Component/Voltron/Views/'.ucfirst($type).'Template.php',null,$voltron->attributes());
		return $view;
	}
	
	private function _renderVoltron(VoltronView $voltron)
	{
		$types = $voltron->types();
		$typeIndex = count($types)-1;
		for($i=$typeIndex;$i >= 0;$i--) {
			try{
				$type = $types[$i];
				return $this->_renderType($type->name(),$voltron);
			}catch(\Exception $e) {}
		}
	}
	
	public function name()
	{
		return 'voltron';
	}
	
	public function render(VoltronView $voltron)
	{
		// if it has children, render children instead
		if(count($voltron->children()) > 0) {
			$content = '';
			foreach($voltron->children() as $child) {
				$content .= $this->render($child); 
			}
			return $content;
		}
		
		// else, render voltron
		return $this->_renderVoltron($voltron);
	}
	
	public function renderChild(VoltronView $voltron,$childName)
	{
		// if it has children, render children instead
		if(count($voltron->children()) > 0) {
			if($child = $voltron->child($childName)) {
				return $this->render($child);
			}
		}
		
		return false;
	}
}