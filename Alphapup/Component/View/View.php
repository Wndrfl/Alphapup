<?php
namespace Alphapup\Component\View;

use Alphapup\Component\Helper\HelperInterface;
use Alphapup\Component\View\Exception\ScaffoldingDoesNotExistException;
use Alphapup\Component\View\Exception\ThemeDirectoryDoesNotExistException;
use Alphapup\Component\View\Exception\ViewDoesNotExistException;
use Alphapup\Core\Kernel\Kernel;

class View implements \ArrayAccess
{
	private 
		$_content = array(),
		$_doctype,
		$_encoding = 'UTF-8',
		$_escape = 'htmlspecialchars',
		$_filters = array(),
		$_filterObjects = array(),
		$_helpers = array(),
		$_htmlVersion,
		$_inlineScripts = array(),
		$_links = array(),
		$_nestedVariables=array(),
		$_kernel,
		$_metas = array(),
		$_ogps = array(),
		$_nested = false,
		$_scaffolding,
		$_scripts = array(),
		$_theme,
		$_title,
		$_variables = array();
	
	public function __construct(Kernel $kernel,$helpers=array())
	{
		$this->setHelpers($helpers);
		$this->setKernel($kernel);
	}
	
	public function __get($key)
	{
		return (isset($this->_variables[$key])) ? $this->_variables[$key] : false;
	}
	
	public function __set($key,$val)
	{
		$this->_variables[$key] = $val;
	}
	
	private function _filter($content)
	{
		foreach($this->_filters as $name) {
			if(!$filter = $this->loader->filter($name)) {
				continue;
			}
			$content = call_user_func(array($filter,'filter'),$content);
		}
		return $content;
	}
	
	private function _importThemeConfig($path)
	{
		include $path;
		
		if(isset($config)) {
			
			// default scaffolding
			if(isset($config['scaffolding'])) {
				$this->scaffolding($config['scaffolding']['plugin'],$config['scaffolding']['file']);
			}
			
			// default css
			if(isset($config['css']) && is_array($config['css'])) {
				foreach($config['css'] as $section => $css) {
					if(is_array($css)) {
						if($section == 'assets') {
							foreach($css as $asset) {
								try {
									$this->css($this->helper('asset')->url($asset));
								}catch(\Exception $e) {
									trigger_error($e->getMessage(),E_USER_ERROR);
								}
							}
						}
						if($section == 'href') {
							foreach($css as $href) {
								$this->css($href);
							}
						}
					}else{
						$this->css($css);
					}
				}
			}elseif(isset($config['css'])) {
				$this->css($config['css']);
			}
			
			// default js
			if(isset($config['scripts']) && is_array($config['scripts'])) {
				if(isset($config['scripts']['top']) && is_array($config['scripts']['top'])) {
					foreach($config['scripts']['top'] as $script) {
						$this->script($script,'top');
					}
				}
				if(isset($config['scripts']['bottom']) && is_array($config['scripts']['bottom'])) {
					foreach($config['scripts']['bottom'] as $script) {
						$this->script($script,'bottom');
					}
				}
			}
			
			// default inline js
			if(isset($config['inlineScripts']) && is_array($config['inlineScripts'])) {
				if(isset($config['inlineScripts']['top']) && is_array($config['inlineScripts']['top'])) {
					foreach($config['inlineScripts']['top'] as $script) {
						$this->inlineScript($script,'top');
					}
				}
				if(isset($config['inlineScripts']['bottom']) && is_array($config['inlineScripts']['bottom'])) {
					foreach($config['inlineScripts']['bottom'] as $script) {
						$this->inlineScript($script,'bottom');
					}
				}
			}

			// default metas
			if(isset($config['metas']) && is_array($config['metas'])) {
				foreach($config['metas'] as $meta) {
					if(!isset($meta['content'])) {
						continue;
					}
					$this->meta(
						$meta['content'],
						((isset($meta['name'])) ? $meta['name'] : null),
						((isset($meta['http-equiv'])) ? $meta['http-equiv'] : null)
					);
				}
			}
			
			// default ogps
			if(isset($config['ogps']) && is_array($config['ogps'])) {
				foreach($config['ogps'] as $ogp) {
					if(!isset($ogp['property']) || !isset($ogp['content'])) {
						continue;
					}
					$this->ogp($ogp['property'],$ogp['content']);
				}
			}
		}
	}
	
	private function _loadView($path,$variables=array(),$tempVariables=array())
	{
		if(!file_exists($path)) {
			throw new ViewDoesNotExistException($path);
		}
		
		$_path = $path;
		
		if($this->nested()) {
			foreach($this->_nestedVariables as $k => $v) {
				$$k = $v;
			}
		}
		
		if(is_array($variables) && $variables) {
			foreach($variables as $k => $v) {
				$$k = $v;
				$this->_nestedVariables[$k] = $v;
			}
		}
		
		if(is_array($tempVariables) && $tempVariables) {
			foreach($tempVariables as $k => $v) {
				$$k = $v;
			}
		}
		
		ob_start(array($this,'tidyUp'));		
		
		$_to_content = false;
		if(!$this->nested()) {
			$_to_content = true;
			$this->nested(true);
		}
		
		include $_path;
		$content = ob_get_contents();
		
		if(!$_to_content) {
			@ob_end_flush();
			return $content;
		}
		
		@ob_end_clean();
		
		$this->nested(false);
		$this->_nestedVariables = array();
		
		return $content;
	}
	
	public function addView($plugin,$path,array $variables=array(),array $tempVariables=array())
	{	
		$this->content(
			$this->_filter(
				$this->view($plugin,$path,array_merge($this->_variables,$variables,$tempVariables))
			)
		);
		return $this;
	}
	
	public function body()
	{
		if(!empty($this->_theme)) {
			ob_start();
			include $this->_theme;
			$body = @ob_get_contents();
			@ob_end_clean();
		}else{
			$body = $this->content();
		}
		return $body;
	}
	
	public function content($content=null)
	{
		if(!is_null($content)) {
			$this->_content[] = $content;
			return $this;
		}
		return (!empty($this->_content)) ? implode('',$this->_content) : '';
	}
	
	public function css($href)
	{
		$this->link('stylesheet','text/css',$href);
		return $this;
	}
	
	public function display()
	{
		echo $this->render();
	}
	
	public function doctype($version=null)
	{
		if(!is_null($version)) {
			switch($version) {
				case '5':
				$this->_doctype = '<!DOCTYPE HTML>';
				break;
				
				case '4':
				$this->_doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
				break;
				
				default:
				$this->_doctype = $version;
				break;
			}
			return $this;
		}
		if(empty($this->_doctype)) {
			$this->doctype(5);
		}
		return $this->_doctype;
	}
		
	public function escape($var)
	{
        if(in_array($this->_escape, array('htmlspecialchars', 'htmlentities'))) {
            return call_user_func($this->_escape,$var,ENT_COMPAT,$this->_encoding);
        }
        if(func_num_args() == 1) {
            return call_user_func($this->_escape,$var);
        }
        $args = func_get_args();
        return call_user_func_array($this->_escape,$args);
    }

	public function helper($helper)
	{
		return (isset($this->_helpers[$helper])) ? $this->_helpers[$helper] : false;
	}
	
	public function htmlVersion($num=null)
	{
		if(is_null($num)) {
			if(empty($this->_htmlVersion)) {
				$this->htmlVersion(5);
			}
			return $this->_htmlVersion;
		}
		switch($num) {
			case '5':
			$this->_htmlVersion = 5;
			
			case '4':
			$this->_htmlVersion = 4;
			
			default:
			return $this;
		}
		
		$this->doctype($this->_htmlVersion);
		return $this;
	}

	public function inlineScript($script,$position='top')
	{
		$position = ($position == 'bottom') ? 'bottom' : 'top';
		if(!isset($this->_inlineScripts[$position])) {
			$this->_scripts[$position] = array();
		}
		$this->_inlineScripts[$position][] = $script;
		return $this;
	}
	
	public function inlineScripts($position)
	{
		$position = ($position == 'bottom') ? 'bottom' : 'top';
		return (isset($this->_inlineScripts[$position])) ? $this->_inlineScripts[$position] : array();
	}

	public function link($rel,$type,$href)
	{
		$this->_links[] = array(
			'rel' => $rel,
			'type' => $type,
			'href' => $href
		);
		return $this;
	}
	
	public function links()
	{
		return $this->_links;
	}
	
	public function meta($content,$name=null,$httpequiv=null)
	{
		$meta = array(
			'content' => $content
		);
		if(!is_null($name)) {
			$meta['name'] = $name;
		}
		if(!is_null($httpequiv)) {
			$meta['http-equiv'] = $httpequiv;
		}
		$this->_metas[] = $meta;
		return $this;
	}
	
	public function metas()
	{
		return $this->_metas;
	}
	
	public function nested($toggle=null)
	{
		if(is_null($toggle)) {
			return $this->_nested;
		}
		$this->_nested = (bool) $toggle;
		return $this;
	}
	
	public function offsetExists($offset)
	{
		return (isset($this->_helpers[$offset])) ? true : false;
	}

	public function offsetGet($offset)
	{
		return (isset($this->_helpers[$offset])) ? $this->_helpers[$offset] : false;
	}
	
	public function offsetSet($offset,$value)
	{
		return $this->_helpers[$offset] = $value;
	}
	
	public function offsetUnset($offset)
	{
		unset($this->_helpers[$offset]);
	}
	
	public function ogp($property,$content)
	{
		$this->_ogps[$property] = $content;
		return $this;
	}
	
	public function ogps()
	{
		return $this->_ogps;
	}
	
	public function pluginPath($plugin,$path)
	{
		return $this->_kernel->pluginPath($plugin,$path);
	}
	
	public function render()
	{
		if(!empty($this->_scaffolding)) {
			ob_start();
			include $this->_scaffolding;
			$output = @ob_get_contents();
			@ob_end_clean();
		}else{
			$output = $this->body();
		}
		
		return $output;
	}
	
	public function scaffolding($plugin,$path)
	{
		if($plugin === false) {
			$this->_setScaffolding(false);
			return $this;
		}
		$path = $this->pluginPath($plugin,$path);
		if(file_exists($path)) {
			$this->_scaffolding = $path;
		}else{
			throw new ScaffoldingDoesNotExistException($path);
		}	
		return $this;
	}
	
	public function script($src,$position='top')
	{	
		$position = ($position == 'bottom') ? 'bottom' : 'top';
		if(!isset($this->_scripts[$position])) {
			$this->_scripts[$position] = array();
		}
		$this->_scripts[$position][] = $src;
		return $this;
	}
	
	public function scripts($position)
	{
		$position = ($position == 'bottom') ? 'bottom' : 'top';
		return (isset($this->_scripts[$position])) ? $this->_scripts[$position] : array();
	}
	
	public function setHelper(HelperInterface $helper)
	{
		$helper->setView($this);
		$this->_helpers[$helper->name()] = $helper;
	}
	
	public function setHelpers($helpers=array())
	{
		foreach($helpers as $helper) {
			$this->setHelper($helper);
		}
	}
	
	public function setKernel(Kernel $kernel)
	{
		$this->_kernel = $kernel;
	}
	
	public function theme($plugin,$dir)
	{	
		$dir = $this->pluginPath($plugin,$dir);
		if(!is_dir($dir)) {
			throw new ThemeDirectoryDoesNotExistException($dir);
		}
		
		$path = $dir.'/Theme.php';
		if(file_exists($path)) {
			$this->_theme = $path;
		}
		
		$path = $dir.'/Config.php';
		
		if(file_exists($path)) {
			$this->_theme_config = $path;
			$this->_importThemeConfig($path);
		}
		return $this;
	}
	
	function tidyUp($input)
	{
		$search = array(
			'/\>[^\S ]+/s', //strip whitespaces after tags, except space
			'/[^\S ]+\</s', //strip whitespaces before tags, except space
			'/(\s)+/s'  // shorten multiple whitespace sequences
		);
		$replace = array(
			'>',
			'<',
			' '
		);
		$output = preg_replace($search, $replace, $input);
		return $output;
	}
	
	public function title($title=null)
	{
		if(!is_null($title)) {
			$this->_title = $title;
			return $this;
		}
		return (!empty($this->_title)) ? $this->_title : '';
	}
	
	public function view($plugin,$path,$variables=array(),$tempVariables=array())
	{
		$path = $this->pluginPath($plugin,$path);
		$content = $this->_loadView($path,$variables,$tempVariables);
		return $content;
	}
}