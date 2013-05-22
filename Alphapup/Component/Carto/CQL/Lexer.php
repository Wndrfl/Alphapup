<?php
namespace Alphapup\Component\Carto\CQL;

use Alphapup\Component\Lexer\BaseLexer;

class Lexer extends BaseLexer
{
	const
		T_NONE				= 0,
		T_CLOSE_BRACKET		= 1,
		T_CLOSE_CURLY_BRACE	= 2,
		T_CLOSE_PARANTHESIS	= 3,
		T_COMMA				= 4,
		T_DIVIDE			= 5,
		T_DOT				= 6,
		T_EQUALS			= 7,
		T_FLOAT				= 8,
		T_GREATER_THAN		= 9,
		T_INTEGER			= 10,
		T_LESS_THAN			= 11,
		T_MINUS				= 12,
		T_MULTIPLY			= 13,
		T_NEGATE			= 14,
		T_OPEN_BRACKET	 	= 15,
		T_OPEN_CURLY_BRACE 	= 16,
		T_OPEN_PARANTHESIS	= 17,
		T_PLACEHOLDER		= 18,
		T_PLUS				= 19,
		T_STRING			= 20,
		
		T_IDENTIFIER		= 100,
		T_AND				= 101,
		T_AS				= 102,
		T_ASC				= 103,
		T_ASSOCIATED		= 104,
		T_BETWEEN			= 105,
		T_BY				= 106,
		T_DESC				= 107,
		T_DISTINCT			= 108,
		T_FALSE				= 109,
		T_FETCH 			= 110,
		T_FROM 				= 111,
		T_IN				= 112,
		T_LIMIT				= 113,
		T_OPTIONAL			= 114,
		T_OR				= 115,
		T_ORDER				= 116,
		T_TRUE				= 117,
		T_WHERE				= 118;
		
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
		if(ctype_alpha($value) || $value[0] === '_') {
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
		if($value[0] == ':' || $value[0] == '?' || ($value[0] == '{')) {
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
			case '[': return self::T_OPEN_BRACKET;
			case ']': return self::T_CLOSE_BRACKET;
            default:
                // Do nothing
                break;
		}
		
		return self::T_NONE;
	}
	
	public function tokenPatterns()
	{
		return array(
			'[a-z_\\\][a-z0-9_\:\\\]*[a-z0-9_]{1}', // identifiers
            '(?:[0-9]+(?:[\.][0-9]+)*)(?:e[+-]?[0-9]+)?', // integers, floats
            "'(?:[^']|'')*'",
            '\{\{[a-z0-9]+\}\}|\?[0-9]*|:[a-z]{1}[a-z0-9_]{0,}', // placeholders
		);
	}
}