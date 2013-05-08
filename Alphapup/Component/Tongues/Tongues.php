<?php
namespace Alphapup\Component\Tongues;

use Alphapup\Component\Tongues\Filter\FilterManager;
use Alphapup\Component\Tongues\TongueString;
use Alphapup\Component\Tongues\TongueManager;
use Alphapup\Component\Exception\PhraseDoesNotExistException;

class Tongues
{
	private
		$_defaultLanguage='en',
		$_filterManager,
		$_tongueManager;
		
	public function __construct(FilterManager $filterManager,TongueManager $tongueManager)
	{	
		$this->setFilterManager($filterManager);
		$this->setTongueManager($tongueManager);
	}
	
	public function setDefaultLanguage($language)
	{
		$this->_defaultLanguage = $language;
	}
	
	public function setFilterManager(FilterManager $filterManager)
	{
		$this->_filterManager = $filterManager;
	}
	
	public function setTongueManager(TongueManager $tongueManager)
	{
		$this->_tongueManager = $tongueManager;
	}
	
	public function string($string,$params=array(),$language=null)
	{
		$language = (is_null($language)) ? $this->_defaultLanguage : $language;
		$tongueString = $this->_tongueManager->string($string,$params,$language);
		$this->_filterManager->applyFilters($tongueString);
		return $tongueString->text();
	}
	
	public function tongue($alias,$params=array(),$language=null)
	{
		$language = (is_null($language)) ? $this->_defaultLanguage : $language;
		try {
			$tongueString = $this->_tongueManager->stringFromAlias($alias,$params,$language);
			$this->_filterManager->applyFilters($tongueString);
			return $tongueString->text();
			
		}catch(\Exception $e) {
			trigger_error($e->getMessage(),E_USER_WARNING);
		}
	}
	

}