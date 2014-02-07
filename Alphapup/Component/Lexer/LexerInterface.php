<?php
namespace Alphapup\Component\Lexer;

interface LexerInterface
{
	public function tokenPatterns();
	public function typeFor($value);
}