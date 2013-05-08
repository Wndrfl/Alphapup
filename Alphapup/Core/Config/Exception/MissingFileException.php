<?php
namespace Alphapup\Core\Config\Exception;

class MissingFileException extends \Exception
{
	public function __construct($path) {
		$message = sprintf('Could not find the config file as %s',$path);
		parent::__construct($message);
	}
}