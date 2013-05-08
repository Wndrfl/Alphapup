<?php
namespace Alphapup\Core\Config\Exception;

class InvalidFileException extends \Exception
{
	public function __construct($path) {
		$message = sprintf('Invalid config file type at %s',$path);
		parent::__construct($message);
	}
}