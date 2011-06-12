<?php
/**
 * The Auth class is a Session managment class, much like Database is for
 * handeling the mysql connection.
 * It has fields for how to verify the user's input ($site, $table and
 * $whereTemplate), fields containing the user data itself ($name and $pass),
 * and the ultimate $check (containing the boolean of whether the user is
 * allowed or not) and $console (containing the error message when rejected)
 * variables.
 */
class InkXMS_Auth {
	// method of verification variables
	var $site;							// the site to which the user wants access
	var $table;							// the table in which to find the access data

	// user data variables
	var $name;							// The entered username
	var $pass;							// The entered password

	// resulting variables
	var $check = false;					// Whether the user is ok or not - the bottomline
	var $console = "";					// The console value
	var $beMenuComponents = array();	// Containing the link(s) to be put in the menu of the page using this object.

	function __construct($Site = "ri", $Table = "auth") {
		global $_POST, $_GET, $_SESSION;
		session_start();

		$this->site = $Site;
		$this->table = $Table;
		$this->beMenuComponents = array('<a href="'.thisURL(array('amend' => 'logout')).'">Logout</a>');

		if(isset($_SESSION[$this->site.'_valid'])) {
			// if the user has a valid session going on.
			if(array_key_exists('logout', $_GET)) {
				// the user wants to log out
				$this->logout();
			}
			else {
				// it's ok, the user is valid, and hasn't logged out
				$this->check = true;
			}
		}
		else if(array_key_exists('name', $_POST) && array_key_exists('pass', $_POST)) {
		// the user has just tried to log in
			$this->name = $_POST['name'];
			$this->pass = $_POST['pass'];
			$this->login();
		}
	}

	// Logs the user out by disabeling it's corresponding _SESSION fields
	function logout() {
		global $_SESSION;

		unset($_SESSION[$this->site.'_valid']);
		unset($_SESSION['user']);
		session_destroy();
	}

	// Logs the user in, asuming the verification and user data variables have been set
	function login() {
		global $_SESSION;

		$query = "SELECT `name` FROM `{$this->table}` WHERE `name`='{$this->name}' AND `pass`=sha1('{$this->pass}') AND (`site` = '{$this->site}' OR `site` = 'ri')";
		$reply = InkXMS_Database::query($query);

		if(mysql_num_rows($reply) > 0) {
			// if the user verification was successful
			// register the user with the session
			$valid_user = $this->name;
			$_SESSION[$this->site.'_valid'] = 'boolean';

			$info = mysql_fetch_assoc($reply);
			mysql_free_result($reply);
			$_SESSION['user'] = $info['name'];

			// make it official
			$this->check = true;
		}
		else {
			$this->console = "username or password is incorrect";
		}
	}

	// Displays the login form for any cat or cowgirl tryin to get in.
	function loginForm() {
		return '
		<form action="'.thisURL(array('delete' => 'logout')).'" method="POST" name="login">
			<table border="0" cellpadding="0" cellspacing="10">'.
			($this->console == "" ? '' : '
				<tr>
					<td colspan="2">'.$this->console.'<br/></td>
				</tr>').'
				<tr>
					<td>Username:</td>
					<td><input name="name" type="text"/></td>
				</tr>
				<tr>
					<td>Password:</td>
					<td><input name="pass" type="password"/></td>
				</tr>
				<tr>
					<td colspan="2" style="text-align: right;">
						<input name="submitted" type="hidden" value="login"/>
						<input type="submit" value="Log in"/>
					</td>
				</tr>
			</table>
		</form>';
	}
}
?>
