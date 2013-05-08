<?php
namespace Alphapup\Core\DependencyInjection\Exception;

class CircularReferenceException extends \Exception
{
	public function __construct($service) {
		$message = sprintf('%s is requesting the a service that creates a circular reference.',$service);
		parent::__construct($message);
	}
}