<?php
namespace Alphapup\Core\Http;

class Session
{
	private
		$_sessionKey = '_alphapup';
		
	private
		$_attributes = array(),
		$_closed = false,
		$_flashes = array(),
		$_idRegenerated = false,
		$_locale = 'en',
		$_oldFlashes = array(),
		$_options = array(),
		$_started = false;
		
	public function __construct(array $options=array())
	{
		$cookieDefaults = session_get_cookie_params();

        $this->_options = array_merge(array(
            'lifetime' => $cookieDefaults['lifetime'],
            'path'     => $cookieDefaults['path'],
            'domain'   => $cookieDefaults['domain'],
            'secure'   => $cookieDefaults['secure'],
            'httponly' => isset($cookieDefaults['httponly']) ? $cookieDefaults['httponly'] : false,
        ), $options);

        // Skip setting new session name if user don't want it
        if(isset($this->_options['name'])) {
            session_name($this->_options['name']);
        }
		
		$this->start();
	}
	
	public function __destruct()
    {
        if (true === $this->_started && !$this->_closed) {
            $this->save();
        }
    }

    public function clear()
    {
        if (false === $this->_started) {
            $this->start();
        }

        $this->_attributes = array();
        $this->_flashes = array();
        //$this->setPhpDefaultLocale($this->locale = $this->defaultLocale);
    }
	
    public function clearFlashes()
    {
        if (false === $this->_started) {
            $this->start();
        }

        $this->_flashes = array();
        $this->_oldFlashes = array();
    }

    /**
     * This method should be called when you don't want the session to be saved
     * when the Session object is garbaged collected (useful for instance when
     * you want to simulate the interaction of several users/sessions in a single
     * PHP process).
     */
    public function close()
    {
        $this->_closed = true;
    }
	
	public function get($name, $default = null)
    {
        return array_key_exists($name, $this->_attributes) ? $this->_attributes[$name] : $default;
    }

    public function getFlash($name, $default = null)
    {
        return array_key_exists($name, $this->_flashes) ? $this->_flashes[$name] : $default;
    }

   	public function getFlashes()
    {
        return $this->_flashes;
    }

    public function has($name)
    {
        return array_key_exists($name, $this->_attributes);
    }

    public function hasFlash($name)
    {
        if (false === $this->_started) {
            $this->start();
        }

        return array_key_exists($name, $this->_flashes);
    }

    public function id()
    {
        if (false === $this->_started) {
            $this->start();
        }

        return session_id();
    }

    public function invalidate()
    {
        $this->clear();
        $this->regenerate(true);
    }

    /**
     * Migrates the current session to a new session id while maintaining all
     * session attributes.
     *
     * @api
     */
    public function migrate()
    {
        $this->regenerate();
    }
	
	public function read($key,$default=null)
	{
		return array_key_exists($key,$_SESSION) ? $_SESSION[$key] : $default;
	}
	
	public function regenerate($destroy = false)
    {
        if ($this->_idRegenerated == true) {
            return;
        }

        session_regenerate_id($destroy);

        $this->_idRegenerated = true;
    }

    public function remove($name)
    {
        if(false === $this->_started) {
            $this->start();
        }

        if (array_key_exists($name, $this->_attributes)) {
            unset($this->_attributes[$name]);
        }
    }
	
	public function removeFlash($name)
    {
        if (false === $this->_started) {
            $this->start();
        }

        unset($this->_flashes[$name]);
    }

    public function replace(array $attributes)
    {
        if(false === $this->_started) {
            $this->start();
        }

        $this->_attributes = $attributes;
    }
	
	public function save()
    {
        if (false === $this->_started) {
            $this->start();
        }

        $this->_flashes = array_diff_key($this->_flashes,$this->_oldFlashes);

        $this->write($this->_sessionKey, array(
            'attributes' => $this->_attributes,
            'flashes'    => $this->_flashes,
            'locale'     => $this->_locale,
        ));
    }
	
	public function set($name, $value)
    {
        if(false === $this->_started) {
            $this->start();
        }

        $this->_attributes[$name] = $value;
    }

    public function setFlash($name, $value)
    {
        if (false === $this->_started) {
            $this->start();
        }

        $this->_flashes[$name] = $value;
        unset($this->_oldFlashes[$name]);
    }

    public function setFlashes($values)
    {
        if (false === $this->_started) {
            $this->start();
        }

        $this->_flashes = $values;
        $this->_oldFlashes = array();
    }
	
	public function start()
    {
        if($this->_started) {
            return;
        }

        session_set_cookie_params(
            $this->_options['lifetime'],
            $this->_options['path'],
            $this->_options['domain'],
            $this->_options['secure'],
            $this->_options['httponly']
        );

        // disable native cache limiter as this is managed by HeaderBag directly
        session_cache_limiter(false);

        if(!ini_get('session.use_cookies') && isset($this->_options['id']) && $this->_options['id'] && $this->_options['id'] != session_id()) {
            session_id($this->_options['id']);
        }

        session_start();

		$attributes = $this->read($this->_sessionKey);
		
		if(isset($attributes['attributes'])) {
            $this->_attributes = $attributes['attributes'];
            $this->_flashes = $attributes['flashes'];
            $this->_locale = $attributes['locale'];
            //$this->setPhpDefaultLocale($this->_locale);

            // flag current flash messages to be removed at shutdown
            $this->_oldFlashes = $this->_flashes;
        }

        $this->_started = true;
    }

    public function write($key, $data)
    {
        $_SESSION[$key] = $data;
    }

}