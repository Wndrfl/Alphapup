<?php
namespace Alphapup\Core\Kernel;

use Alphapup\Core\DependencyInjection\ContainerAwareInterface;
use Alphapup\Core\DependencyInjection\Container;
use Alphapup\Core\Event\EventCenter;
use Alphapup\Core\Http\Request;
use Alphapup\Core\Http\Response;
use Alphapup\Core\Kernel\Event\ControllerEvent;
use Alphapup\Core\Kernel\Exception\ActionDoesNotExistException;

class Dispatcher
{
	private 
		$_container;
	
	public function __construct(Container $container,EventCenter $eventCenter)
	{
		$this->setContainer($container);
		$this->setEventCenter($eventCenter);
	}
	
	public function defaultAction()
	{
		return $this->_container->getConfig('kernel')->get('default_action');
	}
	
	public function defaultController()
	{
		return $this->_container->getConfig('kernel')->get('default_controller');
	}
	
	public function dispatch($c,$a,$p=array(),Response $response)
	{
		$controller = new $c();
		$this->_eventCenter->fire(new ControllerEvent());
		
		$reflection = new \ReflectionClass($c);
		try {
			if($method = $reflection->getMethod($a)) {
				$newParams = array();
				if($parameters = $method->getParameters()) {
					foreach($parameters as $parameter) {
						$name = $parameter->getName();
						if(isset($p[$name])) {
							$newParams[$name] = $p[$name];
						}
					}
					$p = $newParams;
				}
			}
		}catch(\Exception $e) {
			// DO HANDLE EXCEPTION
		}
		
		if($controller instanceof ContainerAwareInterface) {
			$controller->setContainer($this->_container);
		}
		
		ob_start();
		
		// CALL _BEFORELOAD CONTROLLER HOOK
		if(method_exists($controller,'_beforeLoad')) {
			call_user_func_array(array($controller,'_beforeLoad'),array());
		}
		
		if(method_exists($controller,$a)) {
			$returnedResponse = call_user_func_array(array($controller,$a),$p);
		}else{
			throw new ActionDoesNotExistException($c,$a);
		}
		
		// CALL _AFTERLOAD CONTROLLER HOOK
		if(method_exists($controller,'_afterLoad')) {
			call_user_func_array(array($controller,'_afterLoad'),array());
		}
		
		// if the controller simply return it's own
		// response object:
		if($returnedResponse && $returnedResponse instanceof Response) {
			$response = $returnedResponse;
		}else{
			$content = @ob_get_clean();
			$response->append($content);
		}
		
		return $response;
	}
	
	public function dispatchRequest(Request $request,Response $response)
	{
		$c = $request->getControllerName();
		$c = (!empty($c) && $c) ? $c : $this->defaultController();
		$request->setControllerName($c);
		$a = $request->getActionName();
		$a = (!empty($a) && $a) ? $a : $this->defaultAction();
		$request->setActionName($a);
		$p = $request->getParams();
		
		$this->dispatch($c,$a,$p,$response);

	}
	
	public function setDefaultAction($action)
	{
		$this->_defaultAction = $action;
	}
	
	public function setDefaultController($controller)
	{
		$this->_defaultController = $controller;
	}
	
	public function setContainer(Container $container)
	{
		$this->_container = $container;
	}
	
	public function setEventCenter(EventCenter $eventCenter)
	{
		$this->_eventCenter = $eventCenter;
	}
}