<?php
namespace Alphapup\Component\Carto\Proxy;

use Alphapup\Component\Carto\Carto;
use Alphapup\Component\Carto\ArrayCollection;
use Alphapup\Component\Carto\Mapping;

abstract class CollectionProxy extends ArrayCollection
{
	private
		$_association,
		$_carto,
		$_collection,
		$_isDirty = false,
		$_isInitialized = false,
		$_mapping,
		$_owner,
		$_snapshot = array();
		
	public function __construct(Carto $carto,Mapping $mapping,ArrayCollection $collection)
	{
		$this->_carto = $carto;
		$this->_mapping = $mapping;
		$this->_collection = $collection;
	}
		
	public function add($element)
	{
		$this->_collection->setValue(null,$element);
		$this->changed();
	}
		
	public function changed()
	{
		$this->_isDirty = true;
	}
	
	public function collection()
	{
		return $this->_collection;
	}
	
	public function initialize()
	{
		if(!$this->_isInitialized && $this->_association) {
			if($this->_isDirty) {
				// Has NEW objects added through add().
				$newObjects = $this->_collection->values();
			}
			$this->_collection->clear();
			$this->_carto->library()->loadCollection($this);
			$this->takeSnapshot();
			
			// Reattach NEW objects
			if(isset($newObjects)) {
				foreach($newObjects as $obj) {
					$this->add($object);
				}
			}
			$this->_isInitialized = true;
		}
	}
	
	public function insertDiff()
    {
        return array_udiff_assoc($this->_collection->values(), $this->_snapshot,
                function($a, $b) {return $a === $b ? 0 : 1;});
    }
	
	public function isDirty()
	{
		return $this->isDirty();
	}
	
	public function isEmpty()
	{
		$this->initialize();
        return $this->_collection->isEmpty();
	}
	
	public function mapping()
	{
		return $this->_mapping;
	}
	
	public function owner()
	{
		return $this->_owner;
	}
	
	public function remove($key)
	{
		if(isset($this->_collection[$key])) {
			$entity = $this->_collection[$key];
			unset($this->_collection[$key]);
			$this->changed();
		}
		
		// TODO: remove managed element
		// see also : scheduleOrphanRemoval
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
	
	public function takeSnapshot()
    {
        $this->_snapshot = $this->_collection->values();
        $this->_isDirty = false;
    }
}