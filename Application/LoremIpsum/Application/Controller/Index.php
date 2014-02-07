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
		
		$carto = $this->get('carto');
		
		$accounts = $this->get('account_repository');
		if($account1 = $accounts->findById(1)) {
			echo $account1->email();

			$account1->setEmail('asdf@gmail.com');
			echo $account1->email();
			echo $account1->accountUser()->displayName();
			echo $account1->accountUser()->setDisplayName('weenod');
			foreach($account1->comments() as $comment) {
				echo '<br />'.$comment->comment();
				$comment->setComment('mer');
			}
			foreach($account1->groups() as $group) {
				echo '<br />'.$group->name();
				$group->setName('asdfasdf');
			}
			
			$accounts->save($account1)->commit();
		}
		
		$view->title('ALPHAPUP SAYS RELAX');
		$view->theme('LoremIpsum','Application/Theme/Default');
		$view->addView('LoremIpsum','Application/View/Index/Carto.php');
		$view->display();
	}
}