<?php
namespace Alphapup\Component\Carto;

use Alphapup\Component\Carto\Carto;
use Alphapup\Component\Carto\Mapping;

class Repository
{
	private
		$_carto,
		$_mapping;
		
	public function __construct(Carto $carto,Mapping $mapping)
	{
		$this->_carto = $carto;
		$this->_mapping = $mapping;
	}
	
	public function commit()
	{
		$this->_carto->commit($this->_mapping->className());
	}
	
	public function find($id,array $options=array())
	{
		return $this->_carto->librarian($this->_mapping->className())->fetchById($id,null,$options);
	}
	
	public function findBy(array $params=array(),array $orderBy=null,$limit=null,$offset=null,array $options=array())
	{
		return $this->_carto->librarian($this->_mapping->className())->fetchBy($params,$orderBy,$limit,$offset,$options);
	}
	
	public function findByQuery($query,array $options=array())
	{
		return $this->_carto->librarian($this->_mapping->className())->fetchByQuery($query,$options);
	}
	
	public function findOneBy(array $params=array(),array $options=array())
	{
		return $this->_carto->librarian($this->_mapping->className())->fetchOne($params,null,$options);
	}
	
	public function save($entity)
	{
		return $this->_carto->librarian($this->_mapping->className())->persist($entity);
	}
	
	public function select()
	{
		return $this->_carto->librarian($this->_mapping->className())->select();
	}
}