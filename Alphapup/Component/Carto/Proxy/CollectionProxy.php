<?php
namespace Alphapup\Component\Carto\Proxy;

use Alphapup\Component\Carto\ArrayCollection;

abstract class CollectionProxy extends ArrayCollection
{		
	protected
		$_association,
		$_collection,
		$_isDirty = false,
		$_isInitialized = false,
		$_mapping,
		$_owner,
		$_snapshot = array();
	
	public function association()
	{
		return $this->_association();
	}
			
	public function clear()
	{
		$this->__load();
		return parent::clear();
	}

    public function current()
	{
		$this->__load();
		return parent::current();
    }

	public function insertDiff()
    {
        return array_udiff_assoc($this->values(), $this->_snapshot,
                function($a, $b) {return $a === $b ? 0 : 1;});
    }

	public function isDirty()
	{
		return $this->_isDirty;
	}

	public function isEmpty()
	{
		$this->__load();
		return parent::isEmpty();
	}

    public function key()
	{
		$this->__load();
		return parent::key();
    }

	public function offsetExists($offset)
	{
		$this->__load();
		return parent::offsetExists($offset);
	}

	public function offsetGet($offset)
	{
		$this->__load();
		return parent::offsetGet($offset);
	}

	public function offsetSet($offset,$value)
	{
		$this->__load();
		return parent::offsetSet($offset,$value);
	}

	public function offsetUnset($offset)
	{
		$this->__load();
		return parent::offsetUnset($offset);
	}

    public function next()
	{
        $this->__load();
		return parent::next();
    }

	public function owner()
	{
		return $this->_owner;
	}

	public function rewind()
	{
        $this->__load();
		return parent::rewind();
    }

	public function setDirty($bool=true)
	{
		$this->_isDirty = (bool)$bool;
	}

	public function setOwner($entity,array $assoc)
	{
		$this->_owner = $entity;
		$this->_association = $assoc;
	}

	public function setValue($key=null,$value)
	{
		$this->__load();
		return parent::setValue($key,$value);
	}

	public function setValues(array $values=array())
	{
		$this->__load();
		return parent::setValues($values);
	}
	
	public function takeSnapshot()
    {
        $this->_snapshot = $this->values();
        $this->_isDirty = false;
    }

    public function valid()
	{
		$this->__load();
		return parent::valid();
    }

	public function values()
	{
		$this->__load();
		return parent::values();
	}
}