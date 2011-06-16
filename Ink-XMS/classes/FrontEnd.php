<?php
class InkXMS_FrontEnd {
	public static $imagesPerRow = 2;

	public function display() {
		// get tuple from database
		$content = self::_getContent();

		if(!$content) {
			return '<h2>ERROR 404</h2>';
		}
		else {
			// get the xml tree in workable form
			require_once('XmlConversion.php');
			return '<table width="100%">'.$this->_displayTree(InkXMS_XmlConversion::getTree($content)).'</table>';
		}
	}

	protected static final function _getContent() {
		$reply = InkXMS_Database::query("SELECT `content` FROM `" . InkXMS_Config::$space . "_page` WHERE `title`='".mysql_escape_string(InkXMS_Page::$title)."' AND `language`='".mysql_escape_string(InkXMS_Page::$language)."'");
		$row = mysql_fetch_array($reply, MYSQL_ASSOC);
		return isset($row['content']) ? $row['content'] : FALSE;
	}

	protected function _displayTree($tree) {
		$r = '';

		foreach($tree['PARAS'] as $parasNum => $paras) {
			$r .= '<tr><td style="padding: 5px 0px;"><table width="100%">';

			foreach($paras as $blockType => $elementValues) {
				$displayMethod = '_display'.$blockType;
				$r .= $this->$displayMethod($parasNum, $elementValues);
			}

			$r .= '</table></td></tr>';
		}

		return $r;
	}

	protected function _displayTITLE($paramNum, $elementValues) {
		$r = '';
		$count = count($elementValues);
		$width = (int)(100 / $count);

		for($i = 0; $i < $count; $i++) {
			$r .= '<tr><td width="'.$width.'%">'
				.'<div class="title">'.$elementValues[$i].'</div>'
				.'</td></tr>';
		}

		return $r;
	}

	protected function _displayPARA($paramNum, $elementValues) {
		$r = '<tr>';
		$count = count($elementValues);
		$width = (int)(100 / $count);

		for($i = 0; $i < $count; $i++) {
			$r .= '<td width="'.$width.'%">' . $elementValues[$i] . '<br /></td>';
		}

		$r .= '</tr>';

		return $r;
	}

	protected function _displayIMG($paramNum, $elementValues) {
		$r = '';
		$count = count($elementValues);

		for($i = 0; $i < $count; $i++) {
			if(!($i % self::$imagesPerRow)) {
				$r .= '<tr>';
			}

			$width = (int)(100 / self::$imagesPerRow);
			$src = $elementValues[$i]['SRC'][0];
			$url = $elementValues[$i]['URL'][0];
			$ssc = $elementValues[$i]['SSC'][0];

			$r .= '<td class="photo" width="'.$width.'%">'
				.($url != '' ? '<a '.$url.'>' : '')
				.'<img alt="'.$ssc.'" border="0" src="img.php?id='.$src.'" />'
				.($url != '' ? '</a>' : '').'<br />'
				.'<div class="ssc">'.$ssc.'</div>'
				.'</td>';

			// if(the last img has been displayed, there is more than one row to display and a row hasn't just been filled)
			if($i + 1 == $count && $count > self::$imagesPerRow && $count % self::$imagesPerRow != 0) {
				for($j = 0; $j < (self::$imagesPerRow - ($count % self::$imagesPerRow)); $j++) {
					$r .= '<td><br /></td>';
					$i++;
				}
			}

			if((self::$imagesPerRow - ($i % self::$imagesPerRow)) == 1) {
				$r .= '</tr>';
			}
		}

		return $r;
	}

	protected function _displayCELLROW($paramNum, $elementValues) {
		$r = '<tr><td><table style="border: 1px solid black;">';

		foreach($elementValues as $cellRow) {
			$r .= '<tr>';

			foreach($cellRow as $cell) {
				$r .= '<td class="cell">'.$cell.'<br/></td>';
			}

			$r .= '</tr>';
		}

		return $r.'</table></td></tr>';
	}
}