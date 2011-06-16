<?php
ini_set('date.timezone', 'Europe/Amsterdam');

define('ROOT', dirname(__FILE__).'/..');

require_once(ROOT.'/Ink-XMS/Config.php');
InkXMS_Config::init('xSITE_NAMESPACEx');

require_once(ROOT.'/Ink-XMS/classes/Database.php');
InkXMS_Database::configure('xHOSTx', 'xDATABASEx', 'xUSERx', 'xPASSWORDx');

require_once(ROOT.'/Ink-XMS/classes/Page.php');
InkXMS_Page::configure(
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
