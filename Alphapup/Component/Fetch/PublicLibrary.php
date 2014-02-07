<?php
namespace Alphapup\Component\Fetch;

use Alphapup\Component\Fetch\EntityMapper;

class PublicLibrary
{
	private
		$_entities = array();
		
	public function getOrCreateEntity(EntityMapper $entityMapper,array $ids=array())
	{
		$entityFullName = $entityMapper->entityFullName();
		$uid = $this->reduceIds();
		
		if(isset($this->_entities[$entityFullName][$uid]))
			return $this->_entities[$entityFullName][$uid];
		
		$entity = new $entityFullName;
		
		return $entity;
	}
	
	public function reduceIds(array $ids=array())
	{
		return md5(implode('',$ids));
	}
}