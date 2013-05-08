<?php
namespace Alphapup\Core\Kernel\Exception;

class PluginDoesNotExistException extends \Exception
{
	public function __construct($alias) {
		$message = sprintf('The plugin for alias \'%s\' does not exist.',$alias);
		parent::__construct($message);
	}
}