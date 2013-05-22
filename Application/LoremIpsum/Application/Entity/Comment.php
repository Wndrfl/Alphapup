<?php
namespace LoremIpsum\Application\Entity;

/**
 * @Carto\Table (name="comments")
 */
class Comment
{
	/**
	 * @Carto\Id
	 * @Carto\Column (name="id")
	 */
	private $_id;
	
	/**
	 * @Carto\ManyToOne (entity="Account",local="account_id",foreign="id",inversedBy="_comments",lazy=true)
	 */
	private $_account;
	
	/**
	 * @Carto\Column (name="comment")
	 */
	private $_comment;
	
	public function comment()
	{
		return $this->_comment;
	}
	
	public function setAccount($account)
	{
		$this->_account = $account;
	}
	
	public function setComment($comment)
	{
		$this->_comment = $comment;
	}
}