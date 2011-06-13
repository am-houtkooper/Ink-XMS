<?php
ini_set('date.timezone', 'Europe/Amsterdam');

define('ROOT', dirname(__FILE__).'/..');

require_once(ROOT.'/XMS/Config.php');
XMS_Config::init('demo');

require_once(ROOT.'/XMS/classes/Database.php');
XMS_Database::configure('localhost', 'roughdot', 'root', '');

require_once(ROOT.'/XMS/classes/Page.php');
XMS_Page::configure(
	array(
		'English' => array(
			'About us',
			'What we want',
			'Contact us',
		),
		'Nederlands' => array(
			'Wie zijn wij',
			'Wat wij willen',
			'Contact opnemen',
		),
		'Francais' => array(
			'A propos de nous',
			'Ce que nous voulons',
			'Contact',
		),
		'Deutsch' => array(
			'Über uns',
			'Was wir wollen',
			'Kontakt',
		),
	)
);
