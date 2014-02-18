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
	
	public function carto()
	{
		$view = $this->get('view');
		
		$fetch = $this->get('fetch');
		$accounts = $fetch->fetch('accountuser');
		
		foreach($accounts as $account) {
			echo $account->account()->email();
		}
		
		die('Need to set up USE ENTITY to hydrate previously existing entities, like proxies');
		die('Owning entities like AccountUser must currently have a property that corresponds to the local assoc paramater. it might be smarter to make the local paramter be a property name instead of a column name.');
		
		$view->title('ALPHAPUP SAYS RELAX');
		$view->theme('LoremIpsum','Application/Theme/Default');
		$view->addView('LoremIpsum','Application/View/Index/Carto.php');
		$view->display();
		/*
		$view = $this->get('view');
		
		$carto = $this->get('carto');
		
		$accounts = $this->get('account_repository');
		$accounts->test();
		die('success');
		
		$view->title('ALPHAPUP SAYS RELAX');
		$view->theme('LoremIpsum','Application/Theme/Default');
		$view->addView('LoremIpsum','Application/View/Index/Carto.php');
		$view->display();
		* */
	}
}