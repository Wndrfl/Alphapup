<?php
namespace Alphapup\Component\NitPick\Rule;

interface RuleInterface
{
	public function message();
	public function name();
	public function test($value,$options=array());
}