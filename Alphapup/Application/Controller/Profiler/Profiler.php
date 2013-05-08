<?php
namespace Alphapup\Application\Controller\Profiler;

use Alphapup\Core\Controller\Controller;

class Profiler extends Controller
{	
	public function index()
	{
		$this->disableProfiler();
		$view = $this->get('view');
		
		$profiler = $this->get('profiler');
		$view->profiles = array_reverse($profiler->getProfiles());
		
		$view->theme('Alphapup','Application/Theme/Debug');
		$view->addView('Alphapup','Application/View/Profiler/Home/Home.php');
		$view->display();
	}
}