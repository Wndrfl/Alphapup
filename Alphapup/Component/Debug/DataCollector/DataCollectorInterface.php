<?php
namespace Alphapup\Component\Debug\DataCollector;

interface DataCollectorInterface
{
	public function collect();
	public function name();
}