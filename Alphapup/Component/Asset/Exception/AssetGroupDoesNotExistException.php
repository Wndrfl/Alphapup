<?php
namespace Alphapup\Component\Asset\Exception;

class AssetGroupDoesNotExistException extends \Exception
{
	public function __construct($group)
	{
		$message = sprintf('The asset group requested as %s has not been configured, and therefore doesn\'t exist',$group);
		parent::__construct($message);
	}
}