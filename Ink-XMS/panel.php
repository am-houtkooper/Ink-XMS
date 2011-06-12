<?php
require_once('classes/common.inc.php');
require_once('../inc/cfg.php');

require_once('classes/Auth.php');
$auth = new InkXMS_Auth(InkXMS_Config::$space);

if(!$auth->check) {
	$header = 'HTTP/1.1 403 Forbidden';
	header($header);
	echo $header;
}
else {
	require_once('classes/Wrapper.php');
	InkXMS_Wrapper::display('panel');
}