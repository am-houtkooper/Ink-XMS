<?php
require_once('Ink-XMS/classes/common.inc.php');
require_once('inc/cfg.php');

$item = false;
if((isset($_GET['id']) && is_numeric($_GET['id'])) || isset($_GET['name'])) {
	$field = 'id';
	$value = $_GET['id'];
}
else if(array_key_exists('name', $_GET)) {
	$field = 'name';
	$value = '\''.addslashes($_GET['name']).'\'';
}

if(isset($field)) {
	$reply = InkXMS_Database::query(
		'SELECT `image`,`type` FROM `' . InkXMS_Config::$space . '_photo` WHERE `'.$field.'`='.InkXMS_Database::escape($value)
	);
	$item = mysql_fetch_array($reply, MYSQL_ASSOC);
}

if($item)
{
	header('Content-type: '.$item['type']);
	echo $item['image'];
}
else
{
	header('Content-type: image/png');
	echo file_get_contents('Ink-XMS/img/default.png');
}
?>
