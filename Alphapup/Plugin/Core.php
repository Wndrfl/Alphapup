<?php
$config['services'] = array(
	
	'alphapup.cache' => array(
		'class' => 'Alphapup\Core\Cache\Cache',
		'shared' => true,
		'arguments' => array(
			'Finder' => '@alphapup.finder',
			'CacheDir' => '%kernel.cache_dir%'
		)
	),
	
	'alphapup.curl' => array(
		'class' => 'Alphapup\Core\Http\Curl'
	),

	'alphapup.event_center' => array(
		'class' => 'Alphapup\Core\Event\EventCenter',
		'shared' => true,
	),
	
	'alphapup.finder' => array(
		'class' => 'Alphapup\Core\Finder\Finder',
		'shared' => true
	),
	
	'alphapup.http.request' => array(
		'class' => 'Alphapup\Core\Http\Request',
		'shared' => true
	),
	
	'alphapup.http.response' => array(
		'class' => 'Alphapup\Core\Http\Response',
		'shared' => true
	),
	
	'alphapup.kernel.dispatcher' => array(
		'class' => 'Alphapup\Core\Kernel\Dispatcher',
		'arguments' => array(
			'container' => '@alphapup.container',
			'eventCenter' => '@alphapup.event_center'
		)
	),
	
	'alphapup.class_loader' => array(
		'class' => 'Alphapup\Core\ClassLoader\UniversalClassLoader',
		'shared' => true,
		'arguments' => array(
			'triggerError' => false,
		),
	),
	
	'alphapup.debug.exception_handler' => array(
		'class' => 'Alphapup\Core\Debug\ExceptionHandler',
		'shared' => true,
		'arguments' => array(
			'container' => '@alphapup.container',
			'debug' => '%kernel.debug%'
		),
	),
	
	'alphapup.debug.error_handler' => array(
		'class' => 'Alphapup\Core\Debug\ErrorHandler',
		'shared' => true,
		'arguments' => array(
			'displayErrors' => '%kernel.debug%',
			'mailErrors' => 'false'
		),
	),
	
	'alphapup.router' => array(
		'class' => 'Alphapup\Core\Routing\Router',
		'shared' => true,
		'arguments' => array(
			'routes' => '%routes%'
		)
	),
	
	'alphapup.session' => array(
		'class' => 'Alphapup\Core\Http\Session',
		'shared' => true
	),
);