<?php
class InkXMS_Page {
	public static $language;
	public static $title;
	public static $inEditMode = false;

	private static $_menu;
	private static $_amends = array(
		'Lang' => 'SOME_LANGUAGE',
		'title' => null,
		'languageMenu' => array(),
		'languageMenu_flag' => 'NONE',
		'pageMenu' => array(),
		'pageMenu_flag' => 'NONE',
		//, 'content' => '...');
		// content is superfluous, because manipulation of this can be either
		// done by returning a string, or changing the fileLoc.
	);

	public static function configure(array $menu = array()) {
		global $_GET;

		self::$_menu = $menu;

		// If the GET language is an exsistent language, use it.
		reset(self::$_menu);
		self::$language = isset($_GET['language'])
			&& array_key_exists($_GET['language'], self::$_menu)
			? $_GET['language']
			: key(self::$_menu); // grab the first language as default

		// If title is set in the GET array, otherwise the first page in the
		// selected language.
		reset(self::$_menu[self::$language]);
		self::$title = isset($_GET['title'])
			&& in_array($_GET['title'], self::$_menu[self::$language])
			? $_GET['title']
			: current(self::$_menu[self::$language]);

		// If edit is set in the GET array, otherwise mode is normal
		self::$inEditMode = isset($_GET['edit']);
	}

	public static function display() {
		$title = self::_displayTitle(
			self::_generateTitle(self::$_amends['title'])
		);
		$languageMenu = self::_displayLanguageMenu(
			self::_generateLanguageMenuStruct(
				self::$_amends['languageMenu'],
				self::$_amends['languageMenu_flag']
			)
		);
		$siteMenu = self::_displayPageMenu(
			self::_generatePageMenuStruct(
				self::$_amends['pageMenu'],
				self::$_amends['pageMenu_flag']
			)
		);
		$content = self::_displayContent(self::_generateContent());

		require(dirname(__FILE__).'/../../templates/page.phtml');
	}

	private static function _generateTitle($title) {
		return is_null($title) ? self::$title : $title;
	}

	private static function _generateLanguageMenuStruct($amends, $flag) {
		$r = array();
		$count = count(self::$_menu[self::$language]);
		$pageTitleNum = 0;

		for($i = 0; $i < $count; $i++) {
			if(self::$_menu[self::$language][$i] == self::$title) {
				$pageTitleNum = $i;
				break;
			}
		}

		foreach(self::$_menu as $language => $menuItems) {
			$r[$language] = thisURL(
				array(
					'amend' => array(
						'language' => $language,
						'title' => self::$_menu[$language][$pageTitleNum]
					)
				)
			);
		}

		switch($flag) {
			case 'ADD_LEFT':
				$r = array_merge($amends, $r);
				break;
			case 'ADD_RIGHT':
			case 'ADD':
				$r = array_merge($r, $amends);
				break;
			case 'REPLACE':
				$r = $amends;
		}

		return $r;
	}

	private static function _generatePageMenuStruct($amends, $flag) {
		$r = array();
		$count = count(self::$_menu[self::$language]);

		for($i = 0; $i < $count; $i++) {
			$title = self::$_menu[self::$language][$i];
			$r[$title] = thisURL(
				array(
					'amend' => array(
						'language' => self::$language, 'title' => $title
					)
				)
			);
		}

		switch($flag) {
			case 'ADD_LEFT':
				$r = array_merge($amends, $r);
				break;

			case 'ADD_RIGHT':
			case 'ADD':
				$r = array_merge($r, $amends);
				break;

			case 'REPLACE':
				$r = $amends;
		}

		return $r;
	}

	// Doesn't have any amending arguments, because adding those would be
	// superfluous; if another file is desired, the fileLoc can be changed, if a
	// costum string is to be printed, the script can just return that string.
	private static function _generateContent() {
		if(!self::$inEditMode) {
			require_once('FrontEnd.php');
			$frontEnd = new InkXMS_FrontEnd();
			return $frontEnd->display();
		}

		require_once('Auth.php');
		$auth = new InkXMS_Auth(InkXMS_Config::$space);

		if($auth->check) {
			require_once('BackEnd.php');
			$backEnd = new InkXMS_BackEnd();
			return $backEnd->display();
		}

		return $auth->loginForm();
	}

	private static function _displayTitle($title) {
		return $title;
	}

	private static function _displayLanguageMenu($menu) {
		ob_start();
		require(dirname(__FILE__).'/../../templates/language-menu.phtml');
		$r = ob_get_contents();
		ob_clean();

		return $r;
	}

	private static function _displayPageMenu($menu) {
		ob_start();
		require(dirname(__FILE__).'/../../templates/page-menu.phtml');
		$r = ob_get_contents();
		ob_clean();

		return $r;
	}

	private static function _displayContent($content) {
		return $content;
	}

	/**
	 * This is the user's interface to the object, it allows him or her to give
	 * amends to what is automatically generated.
	 * The generateXX functions must adhere to a maximum of two arguments, being
	 * $amends or $customX and one $flag.
	 */
	public static function amend($field, $value, $flag = null) {
		self::$_amends[$field] = $value;

		if(!is_null($flag)) {
			self::$_amends[$field.'_flag'] = $flag;
		}
	}
}