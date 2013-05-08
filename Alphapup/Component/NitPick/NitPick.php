<?php
namespace Alphapup\Component\NitPick;

use Alphapup\Component\Introspect\Introspect;
use Alphapup\Component\NitPick\NitPickResponse;
use Alphapup\Component\NitPick\NitPickTestableInterface;
use Alphapup\Component\NitPick\NitPickTester;
use Alphapup\Component\NitPick\NitPickTestResult;
use Alphapup\Component\NitPick\Rule\RuleInterface;
use Alphapup\Component\Tongues\Tongues;

class NitPick
{
	private
		$_responses=array(),
		$_rules=array(),
		$_tongues;
		
	public function __construct($rules=array(),Tongues $tongues,Introspect $introspect)
	{
		$this->setIntrospect($introspect);
		$this->setRules($rules);
		$this->setTongues($tongues);
	}
	
	public function rule($name)
	{
		if(!isset($this->_rules[$name])) {
			// DO EXCEPTION
			return false;
		}
		return $this->_rules[$name];
	}
	
	public function runTests(NitPickTester $tester)
	{
		$results = array();
		foreach($tester->tests() as $test) {
			
			$rule = $this->rule($test['rule']);
			
			if(!$rule) {
				continue;
			}
			
			$options = $test['options'];
			
			if($success = $rule->test($test['value'],$test['options'])) {
				$message = 'Success!';
			}else{
				$rawMessage = (isset($options['message']) && !is_null($options['message'])) ? $options['message'] : $rule->message();

				$defaultParams = array(
					'value' => $test['value']
				);
				$newMessageParams = (isset($options['messageParams'])) ? $options['messageParams'] : array();
				$messageParams = array_merge($defaultParams,$newMessageParams);

				$message = $this->_tongues->string($rawMessage,$messageParams);
			}
			
			$result = new NitPickTestResult($success,$message,$test['value']);
			$results[] = $result;
		}
		$response = new NitPickResponse($results);
		$this->_responses[] = $response;
		return $response;
	}
	
	public function setIntrospect(Introspect $introspect)
	{
		$this->_introspect = $introspect;
	}
	
	public function setRule($name,RuleInterface $rule)
	{
		$this->_rules[$name] = $rule;
	}
	
	public function setRules($rules=array())
	{
		foreach($rules as $rule) {
			$this->setRule($rule->name(),$rule);
		}
	}
	
	public function setTongues(Tongues $tongues)
	{
		$this->_tongues = $tongues;
	}
	
	public function tester($object)
	{
		return new NitPickTester($object,$this->_introspect->inspectClass($object));
	}
	
	public function validate($object)
	{
		if(!is_object($object)) {
			// DO EXCEPTION
			return false;
		}
		$tester = $this->tester($object);
		$tester->runTests();
		return $this->runTests($tester);
	}
}