<?php
$config['assets'] = array(
	'groups' => array(
		'loremipsum.main.css' => array(
			'files' => array(
				'&alphapup.reset.css',
				'%plugins.loremipsum.path%/Application/Assets/css/main.css'
			),
			'type' => 'css',
			'url' => 'css/main.css',
			'filters' => array(
				//'@filter.css_minifier'
			)
		),
		
		'loremipsum.woozy.js' => array(
			'files' => array(
				'%plugins.loremipsum.path%/Application/Assets/js/woozy.js'
			),
			'type' => 'js',
			'url' => 'js/woozy.js'
		)
	)
);