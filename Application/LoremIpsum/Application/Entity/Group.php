<?php
namespace LoremIpsum\Application\Entity;

/**
 * @Carto\Table (name="groups")
 */
class Group
{
	/**
	 * @Carto\Id 
	 * @Carto\Column (name="id")
	 */
	private $_id;
	
	/**
	 * @Carto\ManyToMany (entity="Account", inversedBy="_groups", local="id", foreign="id", joinTable="accounts_groups", joinColumnLocal="group_id", joinColumnForeign="account_id", lazy=true)
	 */
	private $_accounts = array();
	
	/**
	 * @Carto\Column (name="name")
	 */
	private $_name;
	
	public function accounts()
	{
		return $this->_accounts;
	}
	
	public function name()
	{
		return $this->_name;
	}
	
	public function setAccount($account)
	{
		$this->_accounts[] = $account;
	}
	
	public function setName($name)
	{
		$this->_name = $name;
	}
}