<?php
namespace Profiler\Application\Controller;

use Alphapup\Core\Controller\Controller;

class Profiler extends Controller
{	
	public function index()
	{
		$this->disableProfiler();
		$view = $this->get('view');
		
		$profiler = $this->get('profiler');
		$view->profiles = array_reverse($profiler->getProfiles());
		
		$view->theme('Profiler','Application/Theme/Debug');
		$view->addView('Profiler','Application/View/Profiler/Home/Home.php');
		$view->display();
	}
}