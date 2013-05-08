<?php
namespace Alphapup\Core\Config;

use Alphapup\Core\Config\Exception\InvalidFileException;
use Alphapup\Core\Config\Exception\MissingConfigParamException;
use Alphapup\Core\Config\Exception\MissingFileException;

class ConfigImporter
{
	private $_filer;
	private $_importName = 'import';
	private $_paramName = 'config';

	private function _importPhp(ConfigHandler $handler,$path)
	{
		if(!file_exists($path)) {
			throw new MissingFileException($path);
		}
		include $path;
		
		$importParam = $this->_importName;
		if(isset($$importParam)) {
			foreach($$importParam as $file) {
				$this->import($handler,$file);
			}
		}
		
		$param = $this->_paramName;
		if(isset($$param)) {
			$handler->import($$param);
		}
		
		return $handler;
	}
	
	public function import(ConfigHandler $handler,$path)
	{
		$extension = pathinfo($path, PATHINFO_EXTENSION);
		switch($extension) {
			case 'php':
				try{
					$this->_importPhp($handler,$path);
				}catch(MissingFileException $e) {
					trigger_error($e->getMessage(),E_USER_ERROR);
				}
				break;
				
			default:
				throw new InvalidFileException($path);
				break;
		}
	}
}