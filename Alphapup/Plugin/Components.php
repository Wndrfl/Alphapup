<?php
$config['services'] = array(
	
	'assets.asset_manager' => array(
		'class' => 'Alphapup\Component\Asset\AssetManager',
		'arguments' => array(
			'groups' => '%assets.groups%'
		),
	),
	
	'assets.asset_setup' => array(
		'class' => 'Alphapup\Component\Asset\AssetSetup',
		'shared' => true,
		'arguments' => array(
			'debug' => '%kernel.debug%',
			'finder' => '@alphapup.finder',
			'plugins' => '%plugins%'
		),
	),
	
	'data_collector.config' => array(
		'class' => 'Alphapup\Component\Debug\DataCollector\ConfigDataCollector',
		'shared' => true,
		'arguments' => array(
			'container' => '@alphapup.container'
		),
		'tags' => array(
			'data_collector'
		),
	),
	
	'data_collector.events' => array(
		'class' => 'Alphapup\Component\Debug\DataCollector\EventsDataCollector',
		'shared' => true,
		'arguments' => array(
			'eventCenter' => '@alphapup.event_center'
		),
		'tags' => array(
			'data_collector'
		),
	),
	
	'data_collector.request' => array(
		'class' => 'Alphapup\Component\Debug\DataCollector\RequestDataCollector',
		'shared' => true,
		'arguments' => array(
			'request' => '@alphapup.http.request'
		),
		'tags' => array(
			'data_collector'
		),
	),
	
	'debug.exception_handler' => array(
		'class' => 'Alphapup\Component\Debug\ExceptionHandler',
		'shared' => true,
		'arguments' => array(
			'container' => '@alphapup.container',
			'debug' => '%kernel.debug%'
		),
	),
	
	'debug.error_handler' => array(
		'class' => 'Alphapup\Component\Debug\ErrorHandler',
		'shared' => true,
		'arguments' => array(
			'displayErrors' => '%kernel.debug%',
			'mailErrors' => 'false'
		),
	),
	
	'dexter' => array(
		'class' => 'Alphapup\Component\Dexter\Dexter',
		'shared' => true,
		'arguments' => array(
			'connection' => '@dexter.connection',
			'cache' => '@alphapup.cache',
			'events' => '@alphapup.event_center'
		),
	),
	
	'dexter.data_collector' => array(
		'class' => 'Alphapup\Component\Dexter\DataCollector\DexterDataCollector',
		'shared' => true,
		'arguments' => array(
			'dexter' => '@dexter'
		),
		'tags' => array(
			'data_collector'
		),
	),
	
	'dexter.connection' => array(
		'class' => 'Alphapup\Component\Dexter\DBAL\Connection',
		'shared' => true,
		'arguments' => array(
			'host' => '%database.main.connection.host%',
			'username' => '%database.main.connection.username%',
			'password' => '%database.main.connection.password%',
			'database' => '%database.main.connection.database%',
		),
	),
	
	'filter.css_minifier' => array(
		'class' => 'Alphapup\Component\Filter\Minify\CssMinifier',
		'tags' => array(
			'filter'
		)
	),
	
	'filter.hash' => array(
		'class' => 'Alphapup\Component\Filter\Hash',
		'shared' => true,
		'tags' => array(
			'filter'
		)
	),

	'filter.js_minifier' => array(
		'class' => 'Alphapup\Component\Filter\Minify\JsMinifier',
		'tags' => array(
			'filter'
		)
	),
	
	'helper.asset_helper' => array(
		'class' => 'Alphapup\Component\Helper\AssetHelper',
		'shared' => true,
		'arguments' => array(
			'assetManager' => '@assets.asset_manager',
			'urlHelper' => '@helper.url_helper'
		),
		'tags' => array(
			'view.helper',
		),
	),
	
	'helper.filter' => array(
		'class' => 'Alphapup\Component\Helper\FilterHelper',
		'shared' => true,
		'arguments' => array(
			'filters' => '#filter'
		),
		'tags' => array(
			'view.helper',
		),
	),

	'helper.pagination_helper' => array(
		'class' => 'Alphapup\Component\Helper\PaginationHelper',
		'shared' => false,
		'tags' => array(
			'view.helper',
		),
	),
	
	'helper.url_helper' => array(
		'class' => 'Alphapup\Component\Helper\UrlHelper',
		'shared' => true,
		'arguments' => array(
			'request' => '@alphapup.http.request',
			'router' => '@alphapup.router'
		),
		'tags' => array(
			'view.helper',
		),
	),
	
	'helper.voltron' => array(
		'class' => 'Alphapup\Component\Helper\VoltronHelper',
		'shared' => true,
		'tags' => array(
			'view.helper',
		),
	),
	
	'introspect' => array(
		'class' => 'Alphapup\Component\Introspect\Introspect',
		'shared' => true,
	),
	
	'nitpick' => array(
		'class' => 'Alphapup\Component\NitPick\NitPick',
		'shared' => true,
		'arguments' => array(
			'rules' => '#nitpick.rule',
			'tongues' => '@tongues',
			'introspect' => '@introspect',
		),
	),
	
	'nitpick.rule.is_alpha' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\IsAlpha',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'nitpick.rule.is_alpha_dash' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\IsAlphaDash',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'nitpick.rule.is_alpha_numeric' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\IsAlphaNumeric',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'nitpick.rule.is_equal_to' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\IsEqualTo',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'nitpick.rule.is_greater_than' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\IsGreaterThan',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'nitpick.rule.is_greater_than_or_equal_to' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\IsGreaterThanOrEqualTo',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'nitpick.rule.is_less_than' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\IsLessThan',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'nitpick.rule.is_less_than_or_equal_to' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\IsLessThanOrEqualTo',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'nitpick.rule.is_longer_than' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\IsLongerThan',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'nitpick.rule.is_longer_than_or_equal_to' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\IsLongerThanOrEqualTo',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'nitpick.rule.is_numeric' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\IsNumeric',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'nitpick.rule.is_pattern' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\IsPattern',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'nitpick.rule.is_required' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\IsRequired',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'nitpick.rule.is_shorter_than' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\IsShorterThan',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'nitpick.rule.is_shorter_than_or_equal_to' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\IsShorterThanOrEqualTo',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'nitpick.rule.is_url_friendly' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\IsUrlFriendly',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'nitpick.rule.is_valid_email' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\IsValidEmail',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'nitpick.rule.must_match' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\MustMatch',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'nitpick.rule.must_not_match' => array(
		'class' => 'Alphapup\Component\NitPick\Rule\MustNotMatch',
		'shared' => true,
		'tags' => array(
			'nitpick.rule'
		),
	),
	
	'profiler' => array(
		'class' => 'Alphapup\Component\Debug\Profiler',
		'shared' => true,
		'arguments' => array(
			'settings' => '%profiler%',
			'cache' => '@alphapup.cache'
		)
	),
	
	'tongues' => array(
		'class' => 'Alphapup\Component\Tongues\Tongues',
		'shared' => true,
		'arguments' => array(
			'filterManager' => '@tongues.filter_manager',
			'tongueManager' => '@tongues.tongue_manager',
		),
	),
	
	'tongues.filter_manager' => array(
		'class' => 'Alphapup\Component\Tongues\Filter\FilterManager',
		'shared' => true,
		'arguments' => array(
			'filters' => '#tongues.filter'
		),
	),
	
	'tongues.filter.plural' => array(
		'class' => 'Alphapup\Component\Tongues\Filter\Plural',
		'shared' => true,
		'tags' => array(
			'tongues.filter'
		),
	),
	
	'tongues.filter.possessive' => array(
		'class' => 'Alphapup\Component\Tongues\Filter\Possessive',
		'shared' => true,
		'tags' => array(
			'tongues.filter'
		),
	),
	
	'tongues.tongue_manager' => array(
		'class' => 'Alphapup\Component\Tongues\TongueManager',
		'shared' => true,
		'arguments' => array(
			'phrases' => '%tongues.phrases%',
		),
	),
	
	'view' => array(
		'class' => 'Alphapup\Component\View\View',
		'shared' => false,
		'arguments' => array(
			'kernel' => '@kernel',
			'helpers' => '#view.helper',
		),
	),
	
	'voltron' => array(
		'class' => 'Alphapup\Component\Voltron\VoltronFactory',
		'shared' => true,
		'arguments' => array(
			'typeManager' => '@voltron.type_manager',
			'pluginManager' => '@voltron.plugin_manager',
		),
	),
	
	'voltron.plugin_manager' => array(
		'class' => 'Alphapup\Component\Voltron\VoltronPluginManager',
		'shared' => true,
		'arguments' => array(
			'types' => '#voltron.plugin',
		),
	),
	
	'voltron.plugin.nitpick' => array(
		'class' => 'Alphapup\Component\Voltron\Plugin\NitPickPlugin',
		'shared' => true,
		'arguments' => array(
			'nitPick' => '@nitpick'
		),
		'tags' => array(
			'voltron.plugin',
		),
	),
	
	'voltron.type_manager' => array(
		'class' => 'Alphapup\Component\Voltron\VoltronTypeManager',
		'shared' => true,
		'arguments' => array(
			'types' => '#voltron.type',
		),
	),
	
	'voltron.type.choice' => array(
		'class' => 'Alphapup\Component\Voltron\Type\Choice',
		'shared' => true,
		'tags' => array(
			'voltron.type',
		),
	),

	'voltron.type.date' => array(
		'class' => 'Alphapup\Component\Voltron\Type\Date',
		'shared' => true,
		'tags' => array(
			'voltron.type',
		),
	),
	
	'voltron.type.email' => array(
		'class' => 'Alphapup\Component\Voltron\Type\Email',
		'shared' => true,
		'tags' => array(
			'voltron.type',
		),
	),
	
	'voltron.type.field' => array(
		'class' => 'Alphapup\Component\Voltron\Type\Field',
		'shared' => true,
		'tags' => array(
			'voltron.type',
		),
	),

	'voltron.type.file' => array(
		'class' => 'Alphapup\Component\Voltron\Type\File',
		'shared' => true,
		'tags' => array(
			'voltron.type',
		),
	),
	
	'voltron.type.form' => array(
		'class' => 'Alphapup\Component\Voltron\Type\Form',
		'shared' => true,
		'tags' => array(
			'voltron.type',
		),
	),

	'voltron.type.hidden' => array(
		'class' => 'Alphapup\Component\Voltron\Type\Hidden',
		'shared' => true,
		'tags' => array(
			'voltron.type',
		),
	),

	'voltron.type.input' => array(
		'class' => 'Alphapup\Component\Voltron\Type\Input',
		'shared' => true,
		'tags' => array(
			'voltron.type',
		),
	),

	'voltron.type.password' => array(
		'class' => 'Alphapup\Component\Voltron\Type\Password',
		'shared' => true,
		'tags' => array(
			'voltron.type',
		),
	),
	
	'voltron.type.repeated' => array(
		'class' => 'Alphapup\Component\Voltron\Type\Repeated',
		'shared' => true,
		'tags' => array(
			'voltron.type',
		),
	),

	'voltron.type.text' => array(
		'class' => 'Alphapup\Component\Voltron\Type\Text',
		'shared' => true,
		'tags' => array(
			'voltron.type',
		),
	),
	
	'voltron.type.textarea' => array(
		'class' => 'Alphapup\Component\Voltron\Type\Textarea',
		'shared' => true,
		'tags' => array(
			'voltron.type',
		),
	),
	
	'voltron.type.url' => array(
		'class' => 'Alphapup\Component\Voltron\Type\Url',
		'shared' => true,
		'tags' => array(
			'voltron.type',
		),
	),
);