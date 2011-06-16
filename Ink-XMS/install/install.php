<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang='en' xml:lang='en' xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link href="../includes/xms.css" rel="stylesheet" type="text/css" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Ink-XMS Installation Script</title>
	</head>
	<body>
<?php

// check file permissions
if(Installation::areFilesJustRight()) {
	// write information to config and add entry to auth table
	if(!Installation::isValidSubmit()) {
		// get info for database, server, user and password, login info
		Installation::form();
	}
	else {
		Installation::store();
		Installation::removeDir();
		echo 'done. Enjoy your new website!';
	}
}

// remove installation folder

class Installation {
	private static $_configurationPath = '../../inc/cfg.php';

	private static $_configFields = array(
		'host', 'database', 'user', 'password', 'site_namespace'
	);

	private static function _isDeletable($path) {
		if(!is_readable($path)) {
			echo $path.' NOT READABLE';
			return false;
		}
		else if(!is_writable($path)) {
			echo $path.' NOT WRITABLE';
			return false;
		}
		else if(is_dir($path)) {
			$dh = opendir($path);

			while(($file = readdir($dh)) !== false) {
				if ('.' == $file || '..' == $file) {
					continue;
				}

				if(!self::_isDeletable($path.'/'.$file)) {
					return false;
				}
			}
		}

		return true;
	}

	public static function areFilesJustRight() {
		echo 'checking '.realpath(__DIR__.'/'.self::$_configurationPath).' temperature.. <strong>';
		$configExists = is_file(self::$_configurationPath);
		$configWritable = is_writable(self::$_configurationPath);

		if(!$configExists) {
			echo 'DOES NOT EXIST';
		}
		else if (!$configWritable) {
			echo 'NOT WRITABLE';
		}
		else {
			echo 'OK';
		}

		echo '.</strong><br />';

		echo 'checking '.__DIR__.' temperature.. <strong>';
		$installationDeletable = self::_isDeletable(__DIR__);

		if(!$installationDeletable) {
			echo 'NOT DELETABLE.</strong> probable fix: chmod o+w -R '.__DIR__;
		}
		else {
			echo 'OK.</strong>';
		}

		echo '<br />';

		return $configExists && $configWritable && $installationDeletable;
	}

	public static function isValidSubmit() {
		$fields = self::$_configFields;
		array_push($fields, 'adm_password1', 'adm_password2');
		$r = false;

		if(
			isset($_POST) && count(array_diff($fields, array_keys($_POST))) == 0
		) {
			if($_POST['adm_password1'] == $_POST['adm_password2']) {
				$r = true;
			}
			else {
				echo 'Validating input... <strong>Admin passwords do not match</strong><br />';
			}
		}

		return $r;
	}

	private static function _generatePlaceholder($field) {
		return 'x'.strtoupper($field).'x';
	}

	public function walkGeneratePlaceholders(&$field) {
		$field = self::_generatePlaceholder($field);
	}

	private static function _storeDatabaseConnection() {
		echo 'Write site configuration...';
		$placeholders = self::$_configFields;
		array_walk(
			$placeholders,
			array('self', 'walkGeneratePlaceholders')
		);
		file_put_contents(
			self::$_configurationPath,
			str_replace(
				$placeholders,
				array_intersect_key(
					$_POST, array_fill_keys(self::$_configFields, null)
				),
				file_get_contents(self::$_configurationPath)
			)
		);
		echo ' <strong>DONE.</strong><br />';
	}

	private static function _addAdminLogin() {
		echo 'Write site admin login...';
		InkXMS_Database::query(
			'INSERT INTO `auth` (`name`, `pass`, `site`) VALUES ('
			.'\'admin\','
			.' SHA1(\''.InkXMS_Database::escape($_POST['adm_password1']).'\'),'
			.' \''.$_POST['site_namespace'].'\''
			.')'
		);
		echo ' <strong>DONE.</strong><br />';
	}

	private static function _createTables() {
		$placeholder = self::_generatePlaceholder('site_namespace');
		$seedsPath = __DIR__.'/seeds';
		echo 'Executing auth seed...';
		InkXMS_Database::query(file_get_contents($seedsPath.'/auth.sql'));
		echo ' <strong>DONE.</strong><br />';

		foreach (array('page', 'page_rows', 'photo') as $seed) {
			echo 'Executing '.$seed.' seed...';
			InkXMS_Database::query(
				str_replace(
					$placeholder,
					$_POST['site_namespace'],
					file_get_contents($seedsPath.'/'.$seed.'.sql')
				)
			);
			echo ' <strong>DONE.</strong><br />';
		}
	}

	public static function removeDir() {
		echo 'Removing '.__DIR__.'...';
		unlink(__DIR__);
		echo ' <strong>DONE.</strong><br />';
	}

	public static function store() {
		self::_storeDatabaseConnection();
		require_once(self::$_configurationPath);
		self::_createTables();
		self::_addAdminLogin();
	}

	public static function form() {
		echo <<<HTML
<form method="post">
	<table>
		<tr>
			<td>Host</td>
			<td><input name="host" size="30" type="text" /></td>
		</tr>
		<tr>
			<td>Database</td>
			<td><input name="database" size="30" type="text" /></td>
		</tr>
		<tr>
			<td>Database user</td>
			<td><input name="user" size="30" type="text" /></td>
		</tr>
		<tr>
			<td>Database password</td>
			<td><input name="password" size="30" type="password" /></td>
		</tr>
		<tr>
			<td colspan="2"><br /></td>
		</tr>
		<tr>
			<td>Site namespace (letters and/or numbers only)</td>
			<td><input name="site_namespace" size="30" type="text" /></td>
		</tr>
		<tr>
			<td>Choose admin password</td>
			<td><input name="adm_password1" size="30" type="password" /></td>
		</tr>
		<tr>
			<td>Repeat admin password</td>
			<td><input name="adm_password2" size="30" type="password" /></td>
		</tr>
		<tr>
			<td colspan="2" style="text-align: center">
				<input type="submit" value="Save" />
			</td>
		</tr>
	</table>
</form>
HTML;
	}
}
?>
	</body>
</html>