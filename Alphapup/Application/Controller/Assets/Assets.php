<?php
namespace Alphapup\Application\Controller\Assets;

use Alphapup\Core\Controller\Controller;

class Assets extends Controller
{
	public function index($group,$type)
	{	
		$this->disableProfiler();
		
		$assetManager = $this->get('assets.asset_manager');
		echo $assetManager->render($group);
		$this->get('alphapup.http.response')->setMimeType($type);
	}
	
	public function setup()
	{
		$assetSetup = $this->get('assets.asset_setup');
		$assetSetup->prepareLinks();
	}
}