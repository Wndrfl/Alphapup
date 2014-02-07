<?php
$config['routes'] = array(
	
	'alphapup.profiler' => array(
		'pattern' => 'profiler',
		'controller' => 'Profiler\\Application\\Controller\\Profiler',
		'action' => 'index'
	),
	
	'alphapup.profiler' => array(
		'pattern' => 'profiler/style.css',
		'controller' => 'Profiler\\Application\\Controller\\Profiler',
		'action' => 'style'
	),
	
	'alphapup.profiler.profile' => array(
		'pattern' => 'profiler/profile/{id}',
		'controller' => 'Profiler\\Application\\Controller\\Profile',
		'action' => 'index',
		'requirements' => array(
			'id' => '[a-z0-9]+'
		)
	),
	
	'alphapup.profiler.profile.config' => array(
		'pattern' => 'profiler/profile/{id}/config',
		'controller' => 'Profiler\\Application\\Controller\\Profile',
		'action' => 'config',
		'requirements' => array(
			'id' => '[a-z0-9]+'
		)
	),
	
	'alphapup.profiler.profile.dexter' => array(
		'pattern' => 'profiler/profile/{id}/dexter',
		'controller' => 'Profiler\\Application\\Controller\\Profile',
		'action' => 'dexter',
		'requirements' => array(
			'id' => '[a-z0-9]+'
		)
	),

	'alphapup.profiler.profile.events' => array(
		'pattern' => 'profiler/profile/{id}/events',
		'controller' => 'Profiler\\Application\\Controller\\Profile',
		'action' => 'events',
		'requirements' => array(
			'id' => '[a-z0-9]+'
		)
	),
);