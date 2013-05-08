<?php
namespace Alphapup\Component\Tongues;

use Alphapup\Component\Tongues\Tongue;
use Alphapup\Component\Tongues\TongueString;

class TongueManager
{
	private
		$_slugExp = '|\{\{([A-Za-z0-9:._-]+)\}\}|', // something like {{slug}};
		$_tongues = array();
		
	public function __construct($tongues=array())
	{
		$this->setTongues($tongues);
	}
	
	/**
	 * Replaces {{variable}} and {{tongue:name}} slugs
	**/
	public function buildString($string,$params=array(),$language)
	{
		$handler = $this;
		$string = preg_replace_callback($this->_slugExp,function($matches) use($handler,$params,$language) {
			$fullMatch = $matches[0];
			$slugName = $matches[1];
			$parts = explode(':',$slugName);
			if(count($parts) > 1) {
				$type = $parts[0];
				$slugName = $parts[1];
			}else{
				$type = 'variable';
			}
			switch($type) {
				case 'article':
					break;
				case 'tongue':
					$replacement = $handler->stringFromAlias($slugName,$params,$language)->text();
					break;
				case 'variable':
				default:
					$replacement = (isset($params[$slugName])) ? $params[$slugName] : '';
					break;
			}
			return $replacement;
		},$string);
		return new TongueString($string);
	}
	
	public function setTongue($alias,Tongue $phrase)
	{
		$this->_tongues[$alias] = $phrase;
	}
	
	public function setTongues($phrases=array())
	{
		foreach($phrases as $alias => $translations) {
			$this->setTongue($alias,new Tongue($alias,$translations));
		}
	}
	
	public function tongue($alias)
	{
		if(!isset($this->_tongues[$alias])) {
			throw new PhraseDoesNotExistException($alias);
		}
		return $this->_tongues[$alias];
	}
	
	public function string($string,$params=array(),$language)
	{
		return $this->buildString($string,$params,$language);
	}
	
	public function stringFromAlias($alias,$params=array(),$language)
	{
		$translation = $this->tongue($alias)->translation($language);
		return $this->string($translation,$params,$language);
	}
}