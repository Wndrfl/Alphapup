<?php
namespace Alphapup\Core\DependencyInjection\Exception;

class InvalidClassDescriptionException extends \Exception
{
	public function __construct($id) {
		parent::__construct(sprintf('The configuration for the service \'%s\' is invalid and the service will not be available',$id));
	}
}