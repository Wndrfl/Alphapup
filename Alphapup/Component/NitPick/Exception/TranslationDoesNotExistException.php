<?php
namespace Alphapup\Component\Exception;

class TranslationDoesNotExistException extends \Exception
{
	public function __construct($alias,$language) {
		$message = sprintf('The translation for the tongue %s does not exist in the language %s',$alias,$language);
		parent::__construct($message);
	}
}