<?php
$config['routes'] = array(
	'alphapup.assets.setup' => array(
		'pattern' => 'setup',
		'controller' => 'Alphapup\\Application\\Controller\\Assets\\Assets',
		'action' => 'setup'
	),
	
	'alphapup.profiler' => array(
		'pattern' => 'profiler',
		'controller' => 'Alphapup\\Application\\Controller\\Profiler\\Profiler',
		'action' => 'index'
	),
	
	'alphapup.profiler.profile' => array(
		'pattern' => 'profiler/profile/{id}',
		'controller' => 'Alphapup\\Application\\Controller\\Profiler\\Profile',
		'action' => 'index',
		'requirements' => array(
			'id' => '[a-z0-9]+'
		)
	),
	
	'alphapup.profiler.profile.config' => array(
		'pattern' => 'profiler/profile/{id}/config',
		'controller' => 'Alphapup\\Application\\Controller\\Profiler\\Profile',
		'action' => 'config',
		'requirements' => array(
			'id' => '[a-z0-9]+'
		)
	),
	
	'alphapup.profiler.profile.dexter' => array(
		'pattern' => 'profiler/profile/{id}/dexter',
		'controller' => 'Alphapup\\Application\\Controller\\Profiler\\Profile',
		'action' => 'dexter',
		'requirements' => array(
			'id' => '[a-z0-9]+'
		)
	),

	'alphapup.profiler.profile.events' => array(
		'pattern' => 'profiler/profile/{id}/events',
		'controller' => 'Alphapup\\Application\\Controller\\Profiler\\Profile',
		'action' => 'events',
		'requirements' => array(
			'id' => '[a-z0-9]+'
		)
	),
);