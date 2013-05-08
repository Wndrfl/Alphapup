<?php
namespace Alphapup\Component\Tongues;

use Alphapup\Component\Exception\TranslationDoesNotExistException;

class Tongue
{
	private
		$_name,
		$_translations = array();
		
	public function __construct($name,$translations=array())
	{
		$this->setName($name);
		$this->setTranslations($translations);
	}
	
	public function translation($language)
	{
		if(!isset($this->_translations[$language])) {
			throw new TranslationDoesNotExistException($this->_name,$language);
		}
		
		return $this->_translations[$language];
	}
	
	public function setName($name)
	{
		$this->_name = $name;
	}
	
	public function setTranslation($language,$translation)
	{
		$this->_translations[$language] = $translation;
	}
	
	public function setTranslations($translations=array())
	{	
		foreach($translations as $language => $translation) {
			$this->setTranslation($language,$translation);
		}
	}
}