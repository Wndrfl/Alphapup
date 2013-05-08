<?php
namespace Alphapup\Core\DependencyInjection\Exception;

class ServiceNotFoundException extends \Exception
{
	public function __construct($service) {
		parent::__construct(sprintf('Could not find the service %s',$service));
	}
}