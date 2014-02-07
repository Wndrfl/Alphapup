<?php
namespace Alphapup\Component\Fetch\SQL\Expr;

abstract class BaseExpr
{
	protected
		$_allowedClasses = array(),
		$_parts = array(),
		$_preSeparator = '(',
		$_postSeparator = ')',
		$_separator = ', ';
		
	public function __construct($args=array())
	{
		$this->addMultiple($args);
	}
	
	public function addMultiple($args = array())
    {
        foreach((array) $args as $arg) {
            $this->add($arg);
        }
    }

    public function add($arg)
    {
        if ($arg !== null || ($arg instanceof self && $arg->count() > 0)) {
            // If we decide to keep Expr\Base instances, we can use this check
            if(!is_string($arg)) {
                $class = get_class($arg);

                if (!in_array($class, $this->_allowedClasses)) {
                    //throw new \InvalidArgumentException("Expression of type '$class' not allowed in this context.");
                }
            }

            $this->_parts[] = $arg;
        }
    }

    public function count()
    {
        return count($this->_parts);
    }

	public function __toString()
	{
		if($this->count() == 1) {
            return (string) $this->_parts[0];
        }
        
        return $this->_preSeparator.implode($this->_separator,$this->_parts).$this->_postSeparator;
	}
}