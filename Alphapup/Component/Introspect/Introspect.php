<?php
namespace Alphapup\Component\Introspect;

use Alphapup\Component\Introspect\Introspector\ClassIntrospector;

class Introspect
{
	public function inspectClass($class)
	{
		$reflection = new \ReflectionClass($class);
		$report = new ClassIntrospector($reflection);
		return $report;
	}
}