<?php
class InkXMS_Database {
	private static $_link;

	public static function configure($host, $databaseName, $user, $password) {
		if(!(self::$_link = mysql_connect($host, $user, $password)) || !mysql_select_db($databaseName, self::$_link)) {
			die('Error, Dude someting F$$#$D up, We can\'t open the database');
		}

		mysql_set_charset('utf8', self::$_link);
		register_shutdown_function(array(get_class(), 'close'));
	}

	public static function escape($str) {
		return mysql_real_escape_string($str, self::$_link);
	}

	public static function query($q) {
		$reply = mysql_query($q) or die("<pre><strong>no such luck:</strong>\n\t$q\n</pre>");
		return $reply;
	}

	public static function close() {
		mysql_close(self::$_link);
	}

	/**
	 * Gets all the fields of a mySQL table, can take REQUIRED_ONLY, returning
	 * only the required fields, by checking whether each field can be NULL or
	 * not. You can remove any fields that aren't included in an html form, such
	 * as the auto_increment id.
	 * @param string $table
	 * @param array $remove
	 * @param string $requiredFlag
	 * @return array
	 */
	public static function getFields($table, array $remove = array(), $requiredFlag = null) {
		$r = array();
		$query = 'SHOW FIELDS FROM `'.$table.'`';
		$reply = mysql_query($query) or die("<pre><strong>no such luck:</strong>\n\t$query\n</pre>");

		while($fieldDef = mysql_fetch_assoc($reply)) {
			if(!array_key_exists($fieldDef['Field'], $remove)) {
				if($requiredFlag != 'REQUIRED_ONLY' || $fieldDef['Null'] != 'YES') {
					$r[] = $fieldDef['Field'];
				}
			}
		}
		return $r;
	}

	/**
	 * Gets all the fielddefinitions
	 * @param string $table
	 * @param array $remove
	 * @param string $requiredFlag
	 * @return array
	 */
	public static function getFielddefs($table, array $remove = array(), $requiredFlag = null) {
		$r = array();
		$query = "SHOW FIELDS FROM `".$table."`";
		$reply = mysql_query($query) or die("<pre><strong>no such luck:</strong>\n\t$query\n</pre>");

		while($fieldDef = mysql_fetch_assoc($reply)) {
			if(!array_key_exists($fieldDef['Field'], $remove)) {
				if($requiredFlag != 'REQUIRED_ONLY' || $fieldDef['Null'] != 'YES') {
					$r[$fieldDef['Field']] = $fieldDef;
				}
			}
		}

		return $r;
	}

	/**
	 * Gets the primary key field of a mySQL table by returning the first that fits
	 * the description, multiple primary keys NOT supported.
	 * @param string $table
	 * @return string
	 */
	public static function getPrimaryKey($table) {
		$query = "SHOW FIELDS FROM `".$table."`";
		$reply = mysql_query($query) or die("<pre><strong>no such luck:</strong>\n\t$query\n</pre>");

		while($fieldDef = mysql_fetch_array($reply)) {
			if($fieldDef['Key'] == 'PRI') {
				return $fieldDef['Field'];
			}
		}
	}

	/**
	 * Gets all the required fields of a mySQL table by checking whether each
	 * field can be NULL or not.
	 * @param string $table
	 * @param array $remove
	 */
	public static function getRequiredFields($table, array $remove = array()) {
		$r = array();
		$query = "SHOW FIELDS FROM `".$table."`";
		$reply = mysql_query($query) or die("<pre><strong>no such luck:</strong>\n\t$query\n</pre>");

		while($fieldDef = mysql_fetch_array($reply)) {
			if(!array_key_exists($fieldDef['Field'], $remove)) {
				if($fieldDef['Null'] != 'YES') {
					$r[] = $fieldDef['Field'];
				}
			}
		}

		return $r;
	}
}