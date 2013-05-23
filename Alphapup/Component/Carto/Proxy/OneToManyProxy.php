<?php
namespace Alphapup\Component\Carto\Proxy;

use Alphapup\Component\Carto\ArrayCollection;
use Alphapup\Component\Carto\Librarian\BasicEntityLibrarian;
use Alphapup\Component\Carto\Mapping;
use Alphapup\Component\Carto\Proxy\CollectionProxy;

class OneToManyProxy extends CollectionProxy
{
	private
		$_ids,
		$_librarian;
		
	public $__isInitialized__ = false;
		
	public function __construct(Mapping $mapping,BasicEntityLibrarian $librarian,array $ids=array())
	{
		parent::__construct($mapping);
		$this->_mapping = $mapping;
		$this->_librarian = $librarian;
		$this->_ids = $ids;
	}
	
	public function __load()
	{	
		if(!$this->__isInitialized__ && $this->_association) {
			$this->__isInitialized__ = true;
			if($this->_isDirty) {
				// Has NEW objects added through add().
				$newObjects = $this->values();
			}
			$this->clear();
			$this->_librarian->loadOneToManyCollection(
				$this->_association,
				$this->owner(),
				$this
			);
			$this->takeSnapshot();
			
			// Reattach NEW objects
			if(isset($newObjects)) {
				foreach($newObjects as $obj) {
					$this->add($object);
				}
			}
		}
	}
}