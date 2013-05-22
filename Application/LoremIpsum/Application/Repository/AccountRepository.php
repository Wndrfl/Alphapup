<?php
namespace LoremIpsum\Application\Repository;

use Alphapup\Component\Carto\Carto;
use LoremIpsum\Application\Entity\Account;

class AccountRepository
{
	private
		$_carto,
		$_repo;
		
	public function __construct(Carto $carto)
	{
		$this->_carto = $carto;
		$this->_librarian = $carto->library()->librarian('account');
	}
	
	public function commit()
	{
		$this->_carto->library()->commit();
		return $this;
	}
	
	public function create(Account $entity)
	{
		$entity = $this->save($entity)->commit();
		$this->_repo->commit();
		return $this;
	}
	
	public function findByActive()
	{
		$entities = $this->_repo->findBy(array(
			'active' => true
		));
		return $entities;
	}

	public function findByEmail($email)
	{
		$entity = $this->_librarian->fetchOne(array(
			'_email' => $email
		));
		return $entity;
	}
	
	public function findById($id,array $options=array())
	{
		$entity = $this->_librarian->fetch($id,$options);
		return $entity;
	}
	
	public function findByValidated()
	{
		$entities = $this->_repo->findBy(array(
			'validated' => true
		));
		return $entities;
	}
	
	public function save(Account $account)
	{
		$this->_carto->library()->persist($account);
		return $this;
	}
	
	public function test()
	{
		$cql = $this->_carto->librarian('account')->cqlBuilder();
		$cql
			->fetch('account')
			->associated(array('accountUser'))
			->where(array(
				$cql->isEqualTo($cql->createProperty('account','_id'),'1'),
			));
		$entities = $cql->execute();
		foreach($entities as $entity) {
			echo $entity->displayName();
		}
	}
}