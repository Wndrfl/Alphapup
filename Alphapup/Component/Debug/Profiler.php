<?php
namespace Alphapup\Component\Debug;

use Alphapup\Core\Cache\Cache;
use Alphapup\Component\Debug\DataCollector\DataCollectorInterface;
use Alphapup\Component\Debug\Profile;
use Alphapup\Core\Http\Request;

class Profiler
{
	private 
		$_cache,
		$_cacheLimit = 20,
		$_cacheName = 'profiler',
		$_cacheTtl = 3600,
		$_collectors = array(),
		$_currentProfile,
		$_enabled=true;
	
	public function __construct($settings=array(),Cache $cache)
	{
		$this->setCache($cache);
		if(isset($settings['auto_profile'])) {
			if($settings['auto_profile'] == false) {
				$this->disable();
			}
		}
	}
	
	public function __unset($object)
	{
		$this->save();
		unset($object);
	}
	
	public function add(DataCollectorInterface $dataCollector)
	{
		$this->_collectors[] = $dataCollector;
	}
	
	public function disable()
	{
		$this->_enabled = false;
	}
	
	public function enable()
	{
		$this->_enabled = true;
	}
	
	public function getCurrentProfile()
	{
		if($this->_currentProfile) {
			return $this->_currentProfile;
		}
		
		return false;
	}
	
	public function getProfile($id)
	{
		$profiles = $this->getProfiles();
		return (isset($profiles[$id])) ? $profiles[$id] : false;
	}
	
	public function getProfiles()
	{
		return ($profiles = $this->_cache->get($this->_cacheName)) ? $profiles : array();
	}
		
	public function save(Request $request)
	{
		if(!$this->_enabled) {
			return;
		}
		
		if(!$profiles = $this->getProfiles()) {
			$profiles = array();
		}else{
			$newCount = count($profiles)+1;
			if($newCount > $this->_cacheLimit) {
				$diff = $newCount - $this->_cacheLimit;
				for($i=0;$i<$diff;$i++) {
					array_shift($profiles);
				}
			}
		}
		$profile = new Profile(uniqid());
		$profile->setTime(time());
		$profile->setIp($request->ip());
		$profile->setUrl($request->getUrl('url'));
		foreach($this->_collectors as $collector) {
			$collector->collect();
			$profile->setCollector($collector);
		}
		
		$profiles[$profile->id()] = $profile;
		
		$this->_cache->set($this->_cacheName,$profiles,$this->_cacheTtl);
		
		$this->_currentProfile = $profile;
	}
	
	public function setCache(Cache $cache)
	{
		$this->_cache = $cache;
	}
}