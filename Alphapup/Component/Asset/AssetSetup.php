<?php
namespace Alphapup\Component\Asset;

use Alphapup\Core\Finder\Finder;

class AssetSetup
{
	private
		$_debug,
		$_finder,
		$_plugins = array();
		
	public function __construct($debug,Finder $finder,$plugins=array())
	{
		$this->setDebug($debug);
		$this->setFinder($finder);
		$this->setPlugins($plugins);
	}
	
	public function prepareLinks()
	{	
		foreach($this->_plugins as $alias => $plugin) {
			$pluginPath = $plugin['path'].'/Application/Assets';
			if(is_dir($pluginPath)) {
				$publicPath = $_SERVER['DOCUMENT_ROOT'].'/assets/'.$alias;
				if($this->_debug) {
					$this->_finder->symlink($pluginPath,$publicPath);
				}else{
					$this->_finder->mirror($pluginPath,$publicPath);
				}
			}
		}
	}
	
	public function setDebug($debug)
	{
		$this->_debug = $debug;
	}
	
	public function setFinder(Finder $finder)
	{
		$this->_finder = $finder;
	}
	
	public function setPlugins($plugins=array())
	{
		$this->_plugins = $plugins;
	}
}