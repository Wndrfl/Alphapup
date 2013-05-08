<?php
namespace Alphapup\Core\Http;

class HeadersAlreadySentException extends \Exception
{
	public function __construct($file,$line) {
		parent::__construct(sprintf('Could not send headers because output already started at line %s in %s',$line,$file));
	}
}