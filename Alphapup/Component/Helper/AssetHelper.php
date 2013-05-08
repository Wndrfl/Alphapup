<?php
namespace Alphapup\Component\Helper;

use Alphapup\Component\Asset\AssetManager;
use Alphapup\Component\Helper\BaseHelper;

class AssetHelper extends BaseHelper
{
	private
		$_assetManager,
		$_urlHelper;
		
	public function __construct(AssetManager $assetManager,HelperInterface $urlHelper)
	{
		$this->setAssetManager($assetManager);
		$this->setUrlHelper($urlHelper);
	}
	
	public function name()
	{
		return 'asset';
	}
	
	public function setAssetManager(AssetManager $assetManager)
	{
		$this->_assetManager = $assetManager;
	}
	
	public function setUrlHelper(HelperInterface $urlHelper)
	{
		$this->_urlHelper = $urlHelper;
	}
	
	public function url($group)
	{
		try{
			$group = $this->_assetManager->group($group);
			return $this->_urlHelper->absoluteUrl($group->url());
		}catch(\Exception $e) {
			trigger_error($e->getMessage(),E_USER_ERROR);
		}
	}
}