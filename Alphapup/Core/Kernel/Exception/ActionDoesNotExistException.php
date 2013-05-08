<?php
namespace Alphapup\Core\Kernel\Exception;

class ActionDoesNotExistException extends \Exception
{
	public function __construct($controller,$action) {
		$message = sprintf('The action \'%s\' does not exist in controller \'%s\'',$action,$controller);
		parent::__construct($message);
	}
}