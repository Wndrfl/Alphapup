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
		
	public function __construct(Mapping $mapping,ArrayCollection $collection,Librarian $librarian,array $ids=array())
	{
		parent::__construct($mapping,$collection);
		$this->_librarian = $librarian;
		$this->_ids = $ids;
	}
	
	public function __load()
	{
		if(!$this->__isInitialized__ && $this->_librarian) {
			$this->__isInitialized__ = true;
			
			$this->setValues($this->_librarian->fetchBy($this->_ids));
			unset($this->_library,$this->_ids);
			
			$this->takeSnapshot();
		}
	}
}