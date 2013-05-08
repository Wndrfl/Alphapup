<?php
namespace Alphapup\Component\NitPick;

interface NitPickTestableInterface
{
	public function testWithNitPick(NitPickTester $tester);
}