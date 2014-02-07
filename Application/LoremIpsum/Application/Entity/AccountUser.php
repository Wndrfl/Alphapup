<?php
namespace LoremIpsum\Application\Entity;

/**
 * @Carto\Table (name="accounts_user")
 * @Carto\
 */
class AccountUser
{
	/**
	* @Carto\OneToOne (entity="Account",local="account_id",foreign="id",inversedBy="_accountUser",lazy=true)
	*/
	private $_account;
	
	/**
	 * @Carto\Id
	 * @Carto\Column (name="id")
	 */
	private $_id;
	
	/**
	 * @Carto\Column (name="display_name")
	 */
	private $_displayName;
	
	public function account()
	{
		return $this->_account;
	}
	
	public function displayName()
	{
		return $this->_displayName;
	}
	
	public function setAccount($account)
	{
		$this->_account = $account;
	}
	
	public function setDisplayName($displayName)
	{
		$this->_displayName = $displayName;
	}
}