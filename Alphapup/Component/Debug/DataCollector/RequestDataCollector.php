<?php
namespace Alphapup\Component\Debug\DataCollector;

use Alphapup\Component\Debug\DataCollector\BaseDataCollector;
use Alphapup\Core\Http\Request;

class RequestDataCollector extends BaseDataCollector
{
	private
		$_actionName,
		$_controllerName,
		$_headers,
		$_request;
		
	public function __construct(Request $request)
	{
		$this->_request = $request;
	}
	
	public function actionName()
	{
		return $this->_actionName;
	}
	
	public function collect()
	{
		$this->_controllerName = $this->_request->getControllerName();
		$this->_actionName = $this->_request->getActionName();
		$this->_headers = $this->_request->getHeaders();
	}
	
	public function controllerName()
	{
		return $this->_controllerName;
	}
	
	public function headers()
	{
		return $this->_headers;
	}
	
	public function name()
	{
		return 'request';
	}
}