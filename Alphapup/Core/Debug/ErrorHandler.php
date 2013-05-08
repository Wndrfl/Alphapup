<?php
namespace Alphapup\Core\Debug;

class ErrorHandler
{
	private
		$_errors=array(),
		$_displayErrors,
		$_mailErrors;
		
	public function __construct($displayErrors=false,$mailErrors=false)
	{
		$this->setDisplayErrors($displayErrors);
		$this->setMailErrors($mailErrors);
	}
	
	private function _setError($name,$level,$message,$file,$line,$context)
	{
		$this->_errors[] = array(
			'name'=>$name,
			'level'=>$level,
			'message'=>$message,
			'file'=>$file,
			'line'=>$line,
			'context'=>$context
		);
		if($this->_displayErrors == true) {
			echo '<div style="background:#f4f4f4; color:#4c4a4a; font-size:14px; font-family:Helvetica; line-height:1.4em; padding:10px;"><b>'.$name.':</b> '.$message.' in file '.$file.' on line '.$line.'</div>';
		}
		if($this->_mailErrors != false) {
			$message = 'Error detected on '.$_SERVER['REQUEST_URI']."\n\n".
			'--------------------------------------'."\n\n".
			'Error level: '.$name.' ['.$level.']'."\n".
			'Message: '.$message."\n".
			'File: '.$file."\n".
			'Line: '.$line;
			mail($this->_mailErrors,$name.' Error Caught',$message);
		}
	}
	
	public function handle($level,$message,$file=null,$line=null,$context=null)
	{
		switch($level) {
			
			// fatal
			case E_USER_ERROR:
				$this->_setError('ERROR',E_ERROR,$message,$file,$line,$context);
				exit();
				break;
			
			// non-fatal
			case E_WARNING:
			case E_USER_WARNING:
				$this->_setError('WARNING',E_WARNING,$message,$file,$line,$context);
				break;
			
			// non-fatal
			case E_NOTICE:
			case E_USER_NOTICE:
				$this->_setError('NOTICE',E_NOTICE,$message,$file,$line,$context);
				break;
			
			default:
				$this->_setError('UNKNOWN ERROR',0,$message,$file,$line,$context);
				break;
		}
	}
	
	public function setDisplayErrors($displayErrors=false)
	{
		$this->_displayErrors = $displayErrors;
	}
	
	public function setMailErrors($mailErrors=false)
	{
		$this->_mailErrors = $mailErrors;
	}
}