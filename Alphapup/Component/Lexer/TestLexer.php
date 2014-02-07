<?php
namespace Alphapup\Component\Lexer;

use Alphapup\Component\Lexer\BaseLexer;

class TestLexer extends BaseLexer
{
	const
		T_NONE				= 0,
		T_CLOSE_CURLY		= 1,
		T_CLOSE_PARANTHESIS	= 2,
		T_DIVIDE			= 3,
		T_DOT				= 4,
		T_EQUALS			= 5,
		T_GREATER_THAN		= 6,
		T_INTEGER			= 7,
		T_LESS_THAN			= 8,
		T_MINUS				= 9,
		T_MULTIPLY			= 10,
		T_NEGATE			= 11,
		T_OPEN_CURLY	  	= 12,
		T_OPEN_PARANTHESIS	= 13,
		T_PLACEHOLDER		= 14,
		T_PLUS				= 15,
		T_STRING			= 16,
		
		T_IDENTIFIER		= 100,
		T_ASSOCIATIONS		= 101,
		T_FETCH 			= 102,
		T_LIMIT				= 103,
		T_WHERE				= 104;
		
	public function ignorePatterns()
	{
		return array(
			'\s+', '(.)'
		);
	}
	
	public function typeFor($value)
	{
		// if number
		if(is_numeric($value)) {
			return (strpos($value, '.') !== false || stripos($value, 'e') !== false) 
                    ? self::T_FLOAT : self::T_INTEGER;
		}
		
		// if string
		if($value[0] == "'" || $value[0] == "\"") {
			$value = str_replace("''", "'", substr($value, 1, strlen($value) - 2));
            return self::T_STRING;
		}
		
		// if identifier
		if(ctype_alpha($value)) {
			$name = 'self::T_'.strtoupper($value);
			if(defined($name)) {
				$type = constant($name);
				
				if($type > 100) {
					return $type;
				}
			}
			
			return self::T_IDENTIFIER;
		}
		
		// if placeholder
		if($value[0] == ':' || $value[0] == '?') {
			return self::T_PLACEHOLDER;
		}
		
		switch ($value) {
            case '.': return self::T_DOT;
            case ',': return self::T_COMMA;
            case '(': return self::T_OPEN_PARENTHESES;
            case ')': return self::T_CLOSE_PARENTHESES;
            case '=': return self::T_EQUALS;
            case '>': return self::T_GREATER_THAN;
            case '<': return self::T_LOWER_THAN;
            case '+': return self::T_PLUS;
            case '-': return self::T_MINUS;
            case '*': return self::T_MULTIPLY;
            case '/': return self::T_DIVIDE;
            case '!': return self::T_NEGATE;
            case '{': return self::T_OPEN_CURLY_BRACE;
            case '}': return self::T_CLOSE_CURLY_BRACE;
            default:
                // Do nothing
                break;
		}
		
		return self::T_NONE;
	}
	
	public function tokenPatterns()
	{
		return array(
			'[a-z_\\\][a-z0-9_\:\\\]*[a-z0-9_]{1}',
            '(?:[0-9]+(?:[\.][0-9]+)*)(?:e[+-]?[0-9]+)?',
            "'(?:[^']|'')*'",
            '\?[0-9]*|:[a-z]{1}[a-z0-9_]{0,}'
		);
	}
}