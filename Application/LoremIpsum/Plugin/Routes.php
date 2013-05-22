<?php
$config['routes'] = array(
	'loremipsum' => array(
		'pattern' => '',
		'controller' => 'LoremIpsum\\Application\\Controller\\Index',
		'action' => 'index'
	),
	
	'loremipsum.carto' => array(
		'pattern' => 'carto',
		'controller' => 'LoremIpsum\\Application\\Controller\\Index',
		'action' => 'carto'
	),
);