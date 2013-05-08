<?php
namespace Alphapup\Core\Http;

class Curl
{
	private $_handle;
	private $_options = array();
	private $_postFields = array();
	private $_response;
	private $_responseCode;
	private $_upload = false;
	private $_writeToFile;
	
	private $_opts = array(
		'autoreferer'=>'CURLOPT_AUTOREFERER',
		'binarytransfer'=>'CURLOPT_BINARYTRANSFER',
		'buffersize' => 'CURLOPT_BUFFERSIZE',
		'cainfo' => 'CURLOPT_CAINFO',
		'capath' => 'CURLOPT_CAPATH',
		'closepolicy' => 'CURLOPT_CLOSEPOLICY',
		'connecttimeout' => 'CURLOPT_CONNECTTIMEOUT',
		'connecttimeout_ms' => 'CURLOPT_CONNECTTIMEOUT_MS',
		'cookie' => 'CURLOPT_COOKIE',
		'cookiefile' => 'CURLOPT_COOKIEFILE',
		'cookiejar' => 'CURLOPT_COOKIEJAR',
		'cookiesession'=>'CURLOPT_COOKIESESSION',
		'certinfo'=>'CURLOPT_CERTINFO',
		'crlf'=>'CURLOPT_CRLF',
		'customrequest'=>'CURLOPT_CUSTOMREQUEST',
		'dns_cache_timeout' => 'CURLOPT_DNS_CACHE_TIMEOUT',
		'dns_use_global_cache'=>'CURLOPT_DNS_USE_GLOBAL_CACHE',
		'egdsocket' => 'CURLOPT_EGDSOCKET',
		'encoding' => 'CURLOPT_ENCODING',
		'failonerror'=>'CURLOPT_FAILONERROR',
		'file' => 'CURLOPT_FILE',
		'followlocation'=>'CURLOPT_FOLLOWLOCATION',
		'forbid_reuse'=>'CURLOPT_FORBID_REUSE',
		'fresh_connect'=>'CURLOPT_FRESH_CONNECT',
		'ftp_use_eprt'=>'CURLOPT_FTP_USE_EPRT',
		'ftp_use_epsv'=>'CURLOPT_FTP_USE_EPSV',
		'ftp_create_missing_dirs'=>'CURLOPT_FTP_CREATE_MISSING_DIRS',
		'ftpappend'=>'CURLOPT_FTPAPPEND',
		'ftpascii'=>'CURLOPT_FTPASCII',
		'ftplistonly'=>'CURLOPT_FTPLISTONLY',
		'ftpport' => 'CURLOPT_FTPPORT',
		'ftpsslauth' => 'CURLOPT_FTPSSLAUTH',
		'header'=>'CURLOPT_HEADER',
		'headerfunction' => 'CURLOPT_HEADERFUNCTION',
		'header_out'=>'CURLOPT_HEADER_OUT',
		'http200aliases' => 'CURLOPT_HTTP200ALIASES',
		'httpheader' => 'CURLOPT_HTTPHEADER',
		'http_version' => 'CURLOPT_HTTP_VERSION',
		'httpauth' => 'CURLOPT_HTTPAUTH',
		'httpget'=>'CURLOPT_HTTPGET',
		'httpproxytunnel'=>'CURLOPT_HTTPPROXYTUNNEL',
		'infile' => 'CURLOPT_INFILE',
		'infilesize' => 'CURLOPT_INFILESIZE',
		'interface' => 'CURLOPT_INTERFACE',
		'krb4level' => 'CURLOPT_KRB4LEVEL',
		'low_speed_limit' => 'CURLOPT_LOW_SPEED_LIMIT',
		'low_speed_time' => 'CURLOPT_LOW_SPEED_TIME',
		'max_recv_speed_large' => 'CURLOPT_MAX_RECV_SPEED_LARGE',
		'max_send_speed_large' => 'CURLOPT_MAX_SEND_SPEED_LARGE',
		'maxconnects' => 'CURLOPT_MAXCONNECTS',
		'maxredirs' => 'CURLOPT_MAXREDIRS',
		'mute'=>'CURLOPT_MUTE',
		'netrc'=>'CURLOPT_NETRC',
		'nobody'=>'CURLOPT_NOBODY',
		'noprogress'=>'CURLOPT_NOPROGRESS',
		'nosignal'=>'CURLOPT_NOSIGNAL',
		'passwdfunction' => 'CURLOPT_PASSWDFUNCTION',
		'port' => 'CURLOPT_PORT',
		'post'=>'CURLOPT_POST',
		'postfields' => 'CURLOPT_POSTFIELDS',
		'postquote' => 'CURLOPT_POSTQUOTE',
		'progressfunction' => 'CURLOPT_PROGRESSFUNCTION',
		'protocols' => 'CURLOPT_PROTOCOLS',
		'proxy' => 'CURLOPT_PROXY',
		'proxyauth' => 'CURLOPT_PROXYAUTH',
		'proxyport' => 'CURLOPT_PROXYPORT',
		'proxytype' => 'CURLOPT_PROXYTYPE',
		'proxyuserpwd' => 'CURLOPT_PROXYUSERPWD',
		'put'=>'CURLOPT_PUT',
		'quote' => 'CURLOPT_QUOTE',
		'random_file' => 'CURLOPT_RANDOM_FILE',
		'range' => 'CURLOPT_RANGE',
		'readfunction' => 'CURLOPT_READFUNCTION',
		'redir_protocols' => 'CURLOPT_REDIR_PROTOCOLS',
		'referer' => 'CURLOPT_REFERER',
		'resume_from' => 'CURLOPT_RESUME_FROM',
		'returntransfer'=>'CURLOPT_RETURNTRANSFER',
		'ssl_cipher_list' => 'CURLOPT_SSL_CIPHER_LIST',
		'ssl_verifyhost' => 'CURLOPT_SSL_VERIFYHOST',
		'ssl_verifypeer'=>'CURLOPT_SSL_VERIFYPEER',
		'sslcert' => 'CURLOPT_SSLCERT',
		'sslcertpasswd' => 'CURLOPT_SSLCERTPASSWD',
		'sslcerttype' => 'CURLOPT_SSLCERTTYPE',
		'sslengine' => 'CURLOPT_SSLENGINE',
		'sslengine_default' => 'CURLOPT_SSLENGINE_DEFAULT',
		'sslkey' => 'CURLOPT_SSLKEY',
		'sslkeypasswd' => 'CURLOPT_SSLKEYPASSWD',
		'sslkeytype' => 'CURLOPT_SSLKEYTYPE',
		'sslversion' => 'CURLOPT_SSLVERSION',
		'stderr' => 'CURLOPT_STDERR',
		'timecondition' => 'CURLOPT_TIMECONDITION',
		'timeout_ms' => 'CURLOPT_TIMEOUT_MS',
		'timevalue' => 'CURLOPT_TIMEVALUE',
		'transfertext'=>'CURLOPT_TRANSFERTEXT',
		'unrestricted_auth'=>'CURLOPT_UNRESTRICTED_AUTH',
		'upload'=>'CURLOPT_UPLOAD',
		'url' => 'CURLOPT_URL',
		'useragent' => 'CURLOPT_USERAGENT',
		'userpwd' => 'CURLOPT_USERPWD',
		'verbose'=>'CURLOPT_VERBOSE',
		'writefunction' => 'CURLOPT_WRITEFUNCTION',
		'writeheader' => 'CURLOPT_WRITEHEADER'
	);
	
	public function __construct()
	{
		parent::__construct();
		$this->reset();
	}
	
	public function allowRedirections($toggle=1)
	{
		$this->setOption('followlocation',$toggle);
		return $this;
	}
	
	public function close()
	{
		curl_close($this->_handle);
		if(!empty($this->_writeToFile)) {
			@fclose($this->_writeToFile);
		}
		return $this;
	}
	
	public function execute($url=null)
	{	
		if(!is_null($url)) {
			$this->url($url);
		}else{
			if(!$this->getOption('url')) {
				return false;
			}
		}
		
		$this->init();
		
		if($this->getOption('post') == 1) {
			if($this->_upload) {
				$fields = $this->_postFields;
			}else{
				$fields = array();
				foreach($this->_postFields as $k => $v) {
					$fields[] = $k.'='.$v;
				}
				$fields = implode('&',$fields);
			}
			$this->setOption('postfields',$fields);
		}
		
		foreach($this->_options as $k => $v) {
			curl_setopt($this->_handle,$k,$v);
		}
		
		$this->_response = curl_exec($this->_handle);
		$this->_responseCode = curl_getinfo($c, CURLINFO_HTTP_CODE);
		
		$this->close();
		
		return $this->response();
	}
	
	public function getOption($key)
	{
		$opt = (isset($this->_opts[strtolower($option)])) ? $this->_opts[strtolower($option)] : $option;
		return (isset($this->_options[$opt])) ? $this->_options[$opt] : false;
	}
	
	public function headerFunction($config=array())
	{
		$this->setOption('headerfunction',$config);
		return $this;
	}
	
	public function httpAuthenticate($user,$pass)
	{
		$this->setOption('userpwd',$user.':'.$pass);
		return $this;
	}
	
	public function includeHeaders($toggle=1)
	{
		$this->setOption('header',$toggle);
		return $this;
	}
	
	public function init()
	{
		$this->_handle = curl_init();
		return $this;
	}
	
	public function outputResults($toggle=0)
	{
		$returntransfer = ($toggle = 0) ? 1 : 0;
		$this->setOption('returntransfer',$returntransfer);
		return $this;
	}
	
	public function post($fields,$value=null)
	{
		$this->setOption('post',1);

		$fields = (is_array($fields)) ? $fields : array($fields=>$value);
		foreach($fields as $k => $v) {
			$this->_postFields[$k] = $v;
		}
		
		return $this;
	}
	
	public function reset()
	{
		$this->_options = array();
		$this->outputResults(0);
		return $this;
	}
	
	public function response()
	{
		return (!empty($this->_response)) ? $this->_response : false;
	}
	
	public function responseCode()
	{
		return (!empty($this->_responseCode)) ? $this->_responseCode : false;
	}
	
	public function returnHeaders($toggle=1)
	{
		$this->setOption('header',$toggle);
		return $this;
	}
	
	public function setHeaders($headers=array())
	{
		$this->setOption('httpheader',$headers);
		return;
	}
	
	public function setOption($option,$val)
	{
		$opt = (isset($this->_opts[strtolower($option)])) ? $this->_opts[strtolower($option)] : $option;
		$this->_options[$opt] = $val;
		return $this;
	}
	
	public function timeoutAfter($time)
	{
		$this->setOption('timeout',$time);
		return $this;
	}
	
	public function timeoutConnectionAfter($time)
	{
		$this->setOption('connecttimeout',$time);
		return $this;
	}
	
	public function upload($name,$path)
	{
		$this->_upload = true;
		$this->_postFields[$name] = '@'.$path;
		return $this;
	}

	public function url($url)
	{
		$this->setOption('url',$url);
		return $this;
	}
	
	public function verbose($toggle=1)
	{
		$this->setOption('verbose',$toggle);
		return $this;
	}
	
	public function writeToFile($path)
	{
		if($f = @fopen($path,'w')) {
			$this->_writeToFile = $f;
			$this->setOption('file',$f);
			return $this;
		}
		$this->_error('Curl could not open or write to the file: ',$path);
		return $this;
	}
}