<?php
namespace Alphapup\Component\View\Exception;

class ThemeDirectoryDoesNotExistException extends \Exception
{
	public function __construct($path) {
		$message = sprintf('Theme directory does not exist at \'%s\'',$path);
		parent::__construct($message);
	}
}