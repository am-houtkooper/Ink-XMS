<?php
/**
 * The installation process of setting up the application.
 */
class Installation {
	private static $_configurationPath = '../../inc/cfg.php';

	private static $_configFields = array(
		'host', 'database', 'user', 'password', 'site_namespace'
	);

	/**
	 * Check if all necessary rights are correct.
	 *
	 * @return boolean
	 */
	public static function fileSystemOk() {
		echo 'checking '
			.realpath(self::$_configurationPath)
			.' temperature.. <strong>';
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

		return $configExists && $configWritable;
	}

	/**
	 * Validate if the submitted data is correct.
	 *
	 * @return boolean
	 */
	public static function isValidSubmit() {
		$fields = self::$_configFields;
		array_push($fields, 'adm_password1', 'adm_password2');
		$r = FALSE;

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

	/**
	 * Generate the placeholder version of the field.
	 *
	 * @param string $field
	 * @return string
	 */
	private static function _generatePlaceholder($field) {
		return 'x'.strtoupper($field).'x';
	}

	public function walkGeneratePlaceholders(&$field) {
		$field = self::_generatePlaceholder($field);
	}

	/**
	 * Store the database connection information.
	 */
	private static function _storeDatabaseConnection() {
		echo 'Write site configuration...';
		$placeholders = self::$_configFields;
		array_walk(
			$placeholders,
			array('self', 'walkGeneratePlaceholders')
		);
		$configurationContent = file_get_contents(self::$_configurationPath);

		foreach($placeholders as $placeholder) {
			if(strpos($configurationContent, $placeholder) === FALSE) {
				echo "<strong>FAILED: could not find $placeholder";
				return FALSE;
			}
		}

		file_put_contents(
			self::$_configurationPath,
			str_replace(
				$placeholders,
				array_intersect_key(
					$_POST, array_fill_keys(self::$_configFields, NULL)
				),
				$configurationContent
			)
		);
		echo ' <strong>DONE.</strong><br />';
		return FALSE;
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
		$seedsPath = '../install/seeds';
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
		if(self::_storeDatabaseConnection()) {
			require_once(self::$_configurationPath);
			self::_createTables();
			self::_addAdminLogin();
		}
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