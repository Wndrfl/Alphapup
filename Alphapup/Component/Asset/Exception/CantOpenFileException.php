<?php
namespace Alphapup\Component\Asset\Exception;

class CantOpenFileException extends \Exception
{
	public function __construct($file) {
		$message = sprintf('Could not open the file at %s',$file);
		parent::__construct($message);
	}
}