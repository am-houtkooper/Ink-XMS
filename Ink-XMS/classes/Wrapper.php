<?php
class InkXMS_Wrapper {
	/**
	 * Display the wrapper.
	 * @param string $template
	 * @param string $contentDir
	 */
	public static function display($type) {
		if(isset($_GET['id'])) {
			$fileName = $type.'/'.$_GET['id'];
		}

		if(isset($fileName) && is_file($fileName)) {
			if(isset($_GET['title'])) {
				$title = $_GET['title'];
			}
			else {
				$title = '';
			}

			ob_start();
			require($fileName);
			$content = ob_get_contents();
			ob_clean();
		}
		else {
			$title = '404 Not found.';
			$content = '404 - No content supplied, '.$fileName.' not found.';
		}

		require('templates/'.$type.'.phtml');
	}
}
?>