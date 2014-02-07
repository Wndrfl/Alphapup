<?php
$config['services'] = array(
	'account_repository' => array(
		'class' => 'LoremIpsum\Application\Repository\AccountRepository',
		'shared' => true,
		'arguments' => array(
			'carto' => '@carto',
		),
	),
	
	'account_user_repository' => array(
		'class' => 'LoremIpsum\Application\Repository\AccountUserRepository',
		'shared' => true,
		'arguments' => array(
			'carto' => '@carto',
		),
	),
);