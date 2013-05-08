<?php
namespace Alphapup\Component\View\Exception;

class ScaffoldingDoesNotExistException extends \Exception
{
	public function __construct($path) {
		$message = sprintf('The scaffolding does not exist at \'%s\'',$path);
		parent::__construct($message);
	}
}