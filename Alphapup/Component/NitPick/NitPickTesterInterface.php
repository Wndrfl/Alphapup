<?php
namespace Alphapup\Component\NitPick;

interface NitPickTesterInterface
{
	public function tests();
	public function testValue($value,$rule,array $options=array());
	public function testMethod($method,$rule,array $options=array());
}