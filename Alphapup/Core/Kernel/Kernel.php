<?php
namespace Alphapup\Core\Kernel;

use Alphapup\Core\Cache\Cache;
use Alphapup\Core\Config\ConfigHandler;
use Alphapup\Core\Config\ConfigImporter;
use Alphapup\Component\Debug\ExceptionHandler;
use Alphapup\Core\DependencyInjection\Container;
use Alphapup\Core\Finder\Finder;
use Alphapup\Core\Http\Request;
use Alphapup\Core\Http\Response;
use Alphapup\Core\Kernel\Event\ResponseEvent;
use Alphapup\Core\Kernel\Event\RequestEvent;
use Alphapup\Core\Kernel\Exception\PluginDoesNotExistException;

class Kernel
{	
	
	private 
		$_cachePrefix = 'kernel',
		$_container,
		$_config,
		$_debug,
		$_plugins,
		$_request,
		$_response,
		$_rootDir;
	
	public function __construct($environment,$debug)
	{
		$this->_environment = $environment;
		$this->_debug = (Boolean) $debug;
		$this->_rootDir = $this->getRootDir();
		$this->init();
	}
	
	public function boot()
	{
		$this->registerPlugins();
		$this->buildContainer();
		
		spl_autoload_register(array($this->_container->get('alphapup.class_loader'),'loadClass'),true,true);
		
		register_shutdown_function(array($this,'shutdown'));
		set_error_handler(array($this->_container->get('alphapup.debug.error_handler'),'handle'));
		set_exception_handler(array($this->_container->get('alphapup.debug.exception_handler'),'handle'));
		
		$eventCenter = $this->_container->get('alphapup.event_center');
		
		$this->_request = $request = $this->_container->get('alphapup.http.request');
		$eventCenter->fire(new RequestEvent($request));
		
		$this->_response = $response = $this->_container->get('alphapup.http.response');
		$eventCenter->fire(new ResponseEvent($response));
		
		$router = $this->_container->get('alphapup.router');
		$router->route($request);
		
		$dispatcher = $this->_container->get('alphapup.kernel.dispatcher');
		$dispatcher->dispatch($request,$response);
			
		$response->render();
		
		$this->setConfigCache();
		exit;
	}
	
	public function bootPlugins(Container $container)
	{
		foreach($this->_plugins as $plugin) {
			$plugin->boot($container);
		}
	}
	
	public function buildContainer()
	{	
		$container = new Container($this->getConfig());
		$container->set('kernel',$this);
		
		$this->bootPlugins($container);
		$container->compile();
		
		$this->postBootPlugins($container);
		
		$this->_container = $container;
		return $this->_container;
	}
	
	public function getCacheDir()
	{
		return $this->_rootDir.'/Cache/'.$this->_environment.'/';
	}
	
	public function getConfig($cached=false)
	{
		if(empty($this->_config)) {
			if(!$this->_config = $this->getConfigCache()) {
				$this->_config = new ConfigHandler($this->kernelConfig());
				$this->_config = $this->loadEnvironmentConfig();
			}
		}
		return $this->_config;
	}
	
	public function getConfigCache()
	{
		$path = $this->getCacheDir().'/'.$this->_cachePrefix.'.config';
		if(file_exists($path)) {
			$fh = @fopen($path,'r');
			$contents = fread($fh, filesize($path));
			fclose($fh);
			$config = unserialize($contents);
			return $config;
		}
		return false;
	}
	
	public function getEnvironment()
	{
		return $this->_environment;
	}
	
	public function getRootDir()
	{
		if(null === $this->_rootDir) {
            $r = new \ReflectionObject($this);
            $this->_rootDir = dirname($r->getFileName());
        }

        return $this->_rootDir;
	}
	
	public function handleExceptions($e)
	{
		if($this->_config->kernel->debug) {
			echo $e->__toString();
		}else{
			// DO SOMETHING SECRET
		}
	}
	
	public function importConfigFile($path)
	{
		$importer = new ConfigImporter();
		try {
			$importer->import($this->getConfig(),$path);
		}catch(\Exception $e) {
			// DO LOG
			echo $e->getMessage();
		}
		return $this->getConfig();
	}
	
	public function init()
	{
		if($this->_debug) {
			ini_set('display_errors',1);
            error_reporting(-1);
			set_exception_handler(array($this,'handleExceptions'));
		}else{
			ini_set('display_errors', 0);
		}
	}
	
	public function kernelConfig()
	{
		return array(
			'kernel' => array(
				'cache_dir' => $this->getCacheDir(),
				'debug' => ($this->_debug) ? 1 : 0,
				'environment' => $this->_environment,
				'root_dir' => $this->getRootDir()
			)
		);
	}
	
	public function plugin($alias)
	{
		if(!isset($this->_plugins[$alias])) {
			throw new PluginDoesNotExistException($alias);
		}
		return $this->_plugins[$alias];
	}
	
	public function pluginPath($plugin,$path=null)
	{
		$plugin = $this->plugin($plugin)->dir();
		if(!is_null($path)) {
			$plugin .= '/'.ltrim($path,'/');
		}
		return $plugin;
	}
	
	public function postBootPlugins(Container $container)
	{
		foreach($this->_plugins as $plugin) {
			if(method_exists($plugin,'postBoot')) {
				$plugin->postBoot($container);
			}
		}
	}
	
	public function registerPlugin($alias,PluginInterface $plugin)
	{
		$setup = array(
			'plugins' => array(
				$alias => array(
					'path' => $plugin->dir()
				)
			)
		);
		$this->getConfig()->import($setup);
		$this->_plugins[$alias] = $plugin;
	}
	
	public function registerPlugins()
	{
		$plugins = $this->buildPlugins();
		foreach($plugins as $alias => $plugin) {
			$this->registerPlugin($alias,$plugin);
		}
	}
	
	public function setConfigCache()
	{
		if(!$this->_debug) {
			file_put_contents($this->getCacheDir().'/'.$this->_cachePrefix.'.config',serialize($this->getConfig()));
		}
	}
	
	public function shutdown()
	{
		$this->_container->get('profiler')->save($this->_request);
	}
}