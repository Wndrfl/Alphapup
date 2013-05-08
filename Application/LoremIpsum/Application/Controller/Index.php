<?php
namespace LoremIpsum\Application\Controller;

use Alphapup\Core\Controller\Controller;

class Index extends Controller
{
	public function index()
	{	
		$view = $this->get('view');
		
		$view->script('//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js');
		
		$view->title('ALPHAPUP SAYS RELAX');
		$view->theme('LoremIpsum','Application/Theme/Default');
		$view->addView('LoremIpsum','Application/View/Index/Welcome.php');
		$view->display();
	}
}