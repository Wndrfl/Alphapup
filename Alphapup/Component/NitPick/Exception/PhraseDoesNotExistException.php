<?php
namespace Alphapup\Component\Exception;

class PhraseDoesNotExistException extends \Exception
{
	public function __construct($alias) {
		$message = sprintf('The phrase for %s does not exist',$alias);
		parent::__construct($message);
	}
}