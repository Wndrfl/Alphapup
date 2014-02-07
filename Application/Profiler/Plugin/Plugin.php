<?php
namespace Profiler\Plugin;

use Alphapup\Core\DependencyInjection\Container;
use Alphapup\Core\Http\Response;
use Alphapup\Core\Kernel\PluginInterface;

class Plugin implements PluginInterface
{
	private
		$_dispatcher,
		$_profiler;
		
	public function appendToolbar($event)
	{
		$this->_profiler->save($event->request());
		
		// Create toolbar
		$toolbarResponse = $this->_dispatcher->dispatch('Profiler\\Application\\Controller\\Toolbar','index',array(),new Response());

		/* split the string contained in $html in three parts: 
		 * everything before the <body> tag
		 * the body tag with any attributes in it
		 * everything following the body tag
		 */
		$matches = preg_split('/(<\/body.*?>)/i', $event->response()->content(), -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		$html = $matches[0] . $matches[1] . $toolbarResponse->content() . $matches[2];
		
		$event->response()->setContent($html);
	}
	
	public function boot(Container $container)
	{
		$container->importConfigFile(__DIR__.'/Config.php');
	}
	
	public function dir()
	{
		return realpath(__DIR__.'/..');
	}
	
	public function postBoot(Container $container)
	{
		$this->_dispatcher = $container->get('alphapup.kernel.dispatcher');
		$this->_profiler = $container->get('profiler');
		
		$container
			->get('alphapup.event_center')
			->addListener('kernel.prerender',array($this,'appendToolbar'))
			->addListener('kernel.shutdown',array($this,'shutdownKernel'));
		
	}
	
	public function shutdownKernel($event)
	{
		// TODO: any cleanup
	}
}