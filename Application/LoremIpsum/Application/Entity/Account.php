<?php
namespace LoremIpsum\Application\Entity;

use LoremIpsum\Application\Entity\AccountUser;

/**
 * @Carto\Table (name="accounts")
 */
class Account
{
	/**
	 * @Carto\OneToOne (entity="AccountUser",mappedBy="_account",lazy=true)
	 */
	private $_accountUser;
	
	/**
	 * @Carto\Column (name="active")
	 */
	private	$_active = false;
	
	/**
	 * @Carto\OneToMany (entity="Comment",mappedBy="_account",lazy=true)
	 */
	private $_comments = array();
	
	/**
	 * @Carto\Column (name="email")
	 * @NitPick\isValidEmail
	 */
	private $_email;
	
	/**
	 * @Carto\ManyToMany (entity="Group",mappedBy="_accounts",lazy=true)
	 */
	private $_groups = array();
	
	/**
	 * @Carto\Id (generation="auto")
	 * @Carto\Column (name="id")
	 */
	private $_id = 0;
	
	/**
	 * @Carto\Column (name="password")
	 * @NitPick\isRequired
	 */
	private $_password;
	
	/**
	 * @Carto\Column (name="salt")
	 * @NitPick\isRequired
	 */
	private $_salt;
	
	/**
	 * @Carto\Column (name="validated")
	 */
	private $_validated = false;

	public function __construct($id=null)
	{
		$this->_id = $id;
		if(is_null($id)) {
			$this->setSalt();
		}
	}
	
	public function accountUser()
	{
		return $this->_accountUser;
	}
	
	public function comments()
	{
		return $this->_comments;
	}
	
	public function email()
	{
		return $this->_email;
	}
	
	public function groups()
	{
		return $this->_groups;
	}
	
	public function hash($str) {
		return hash('sha256',$this->salt().$str); 
	}
	
	public function id()
	{
		return $this->_id;
	}
	
	public function password()
	{
		return $this->_password;
	}
	
	public function salt()
	{
		if(empty($this->_salt)) {
			$this->setSalt();
		}
		return $this->_salt;
	}
	
	public function setAccountUser(AccountUser $accountUser)
	{
		$this->_accountUser = $accountUser;
	}
	
	public function setActive($active)
	{
		$this->_active = (bool)$active;
		return $this;
	}
	
	public function setEmail($email)
	{	
		$this->_email = $email;
		return $this;
	}
	
	public function setPassword($password)
	{
		$this->_password = $this->hash($password);
		return $this;
	}
	
	public function setSalt()
	{
		$a = range('a','z');
		$A = range('A','Z');
		$n = range(0,9);
		$s = array_merge($a,$A,$n);
		$salt = substr(str_shuffle(implode('',$s)),0,40);
		$this->_salt = $salt;
		return $this;
	}
	
	public function setValidated($validated)
	{
		$this->_validated = (bool) $validated;
		return $this;
	}
	
	public function validated()
	{
		return $this->_validated;
	}
}