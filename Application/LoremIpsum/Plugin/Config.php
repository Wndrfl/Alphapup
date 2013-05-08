<?php
$import = array(
	__DIR__.'/Assets.php',
	__DIR__.'/Routes.php',
	__DIR__.'/Services.php',
	__DIR__.'/Tongues.php'
);

$config['kernel'] = array(
	'default_controller' => 'LoremIpsum\\Application\\Controller\\Index',
	'default_action' => 'index'
);