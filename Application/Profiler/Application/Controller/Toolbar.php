<?php
namespace Profiler\Application\Controller;

use Alphapup\Core\Controller\Controller;

class Toolbar extends Controller
{	
	public function index()
	{
		$this->disableProfiler();
		$view = $this->get('view');
		
		$profiler = $this->get('profiler');
		$profile = $profiler->getCurrentProfile();
		if(!$profile) {
			return;
		}
		
		$view->profileId = $profile->id();
		
		// BASIC INFO
		$view->actionName = $profile->collector('request')->actionName();
		$view->controllerName = $profile->collector('request')->controllerName();
		$view->totalQueries = count($profile->collector('dexter')->queries());
		
		$view->addView('Profiler','Application/View/Toolbar/Toolbar.php');
		$view->display();
	}
	
	function style()
	{
		$view = $this->get('view');
		$view->addView('Profiler','Application/Assets/css/debug/profiler.css');
		
		$response = new Response();
		$response->setMimeType('css');
		$response->append($view->render());
	}
}