<?php
namespace Alphapup\Component\View\Exception;

class ViewDoesNotExistException extends \Exception
{
	public function __construct($path) {
		$message = sprintf('The view does not exist at \'%s\'',$path);
		parent::__construct($message);
	}
}