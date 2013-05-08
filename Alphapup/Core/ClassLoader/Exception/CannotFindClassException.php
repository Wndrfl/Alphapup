<?php
namespace Alphapup\Core\ClassLoader\Exception;

class CannotFindClassException extends \Exception
{
	public function __construct($class) {
		$message = sprintf('Cannot find the file for the class \'%s\'',$class);
		parent::__construct($message);
	}
}