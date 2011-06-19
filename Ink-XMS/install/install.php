<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang='en' xml:lang='en' xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link href="../includes/xms.css" rel="stylesheet" type="text/css" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Ink-XMS Installation Script</title>
	</head>
	<body>
<?php
require_once('../classes/Installation.php');

// check file permissions
if(Installation::fileSystemOk()) {
	// write information to config and add entry to auth table
	if(!Installation::isValidSubmit()) {
		// get info for database, server, user and password, login info
		Installation::form();
	}
	else {
		// store the configuration, add the contents and add admin user
		Installation::store();
		echo 'done. Enjoy your new website! Remove the '.__DIR__.' directory.';
	}
}
?>
	</body>
</html>