<?php
namespace Alphapup\Component\Filter;

interface FilterInterface
{
	public function filter($value);
	public function name();
}