<?php
namespace Alphapup\Component\Debug\DataCollector;

use Alphapup\Component\Debug\DataCollector\DataCollectorInterface;

abstract class BaseDataCollector implements DataCollectorInterface
{
	private
		$_data;

	public function __construct() {}
}