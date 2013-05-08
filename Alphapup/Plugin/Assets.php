<?php
$config['assets'] = array(
	'groups' => array(
		'alphapup.error.css' => array(
			'files' => array(
				'%plugins.alphapup.path%/Application/Assets/css/tools/reset.css',
				'%plugins.alphapup.path%/Application/Assets/css/tools/clearfix.css',
				'%plugins.alphapup.path%/Application/Assets/css/alphapup/main.css',
				'%plugins.alphapup.path%/Application/Assets/css/error/error.css'
			),
			'filters' => array(
				'@filter.css_minifier'
			),
			'type' => 'css',
			'url' => 'css/alphapup/error.css'
		),
		
		'alphapup.main.css' => array(
			'files' => array(
				'&alphapup.reset.css',
				'%plugins.alphapup.path%/Application/Assets/css/alphapup/main.css',
			),
			'type' => 'css'
		),
		
		'alphapup.profiler.css' => array(
			'files' => array(
				'&alphapup.main.css',
				'%plugins.alphapup.path%/Application/Assets/css/debug/profiler.css'
			),
			'filters' => array(
				'@filter.css_minifier'
			),
			'type' => 'css',
			'url' => 'css/alphapup/profiler.css'
		),
		
		'alphapup.reset.css' => array(
			'files' => array(
				'%plugins.alphapup.path%/Application/Assets/css/tools/reset.css',
				'%plugins.alphapup.path%/Application/Assets/css/tools/clearfix.css',
			),
			'type' => 'css'
		),
	)
);