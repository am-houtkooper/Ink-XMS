<?php
require_once('FrontEnd.php');

class InkXMS_BackEnd extends InkXMS_FrontEnd {
	private static $_console;
	private static $_width = 800;

	public function display() {
		global $_POST;

		// handle submitted data
		if(array_key_exists('submitted', $_POST)
			&& $_POST['submitted'] == 'xmsServerAction'
		) {
			self::$_console[] = self::_writeData();
		}

		// get tuple from database
		$content = parent::_getContent();

		if(!$content) {
			return '<h2>ERROR 404</h2>';
		}
		else {
			require_once('XmlConversion.php');
			$tree = InkXMS_XmlConversion::getTree($content);
			array_walk_recurs($tree, 'walkbr2nl');
			return self::_displayConsole()
				.'<form action="'.thisURL().'" method="post" name="interface">'
				.'<table class="backend" width="100%">'.parent::_displayTree($tree).'</table>'
				.'<center>'
				.'<input name="submitted" type="hidden" value="xmsServerAction" />'
				.'<input class="submit" type="submit" value="Save" />'
				.'<input class="button" type="button" onclick="window.location.reload(true);" value="Cancel" />'
				.'</center>'
				.'</form>';
		}
	}

	private static function _displayConsole() {
		return '<div id="console"><span style="font-size:16px; font-weight:bold; text-align:center;">Ink XMS 1.5.8.22</span><br />'
			.(count(self::$_console) > 0 ? implode('<br />', self::$_console) : '')
			.'<div style="text-align:right;"><a href="'.thisURL(array('delete' => 'edit')).'">' . $_SERVER['HTTP_HOST'] . '</a> | <a href="'.thisURL(array('amend' => array('logout' => ''))).'">Log off</a></div>'
			.'</div>';
	}

	/**
	 * Get the width of an element.
	 * @param int $numSiblings
	 * @return number
	 */
	private static function _getElementWidthPixels($numSiblings) {
		return (int)((self::$_width - $numSiblings * 22) / $numSiblings);
	}

	protected function _displayCELLROW($paramNum, $elementValues) {
		$content = '<table>';
		$names = array();

		for($i = 0; $i < count($elementValues); $i++) {
			$cellCount = count($elementValues[$i]['CELL']);
			$content .= '<tr>';

			for($j = 0; $j < $cellCount; $j++) {
				$name = 'PAGE_PARAS_'.$paramNum.'_CELLROW_'.$i.'_CELL_'.$j;
				$names[] = $name;
				$length = strlen($elementValues[$i]['CELL'][$j]);

				if($length == 0) {
					$length = 10;
				}

				$content .= '<td class="cell">'
					.'<input name="'.$name.'" size="'.$length.'" type="text" value="'.$elementValues[$i]['CELL'][$j].'" />'
					.'</td>';
			}

			$content .= '</tr>';
		}

		$content .= '</table>';
		return '<tr><td class="block cellrow-block">'
			.self::_editBox($content, $names)
			.'</td></tr>';
	}

	protected function _displayIMG($paramNum, $elementValues) {
		$r = '';
		$count = count($elementValues);
		$widthPct = (int)(100 / $count);

		$srcNamePattern = 'PAGE_PARAS_%s_IMG_%s_SRC_0';
		$sscNamePattern = 'PAGE_PARAS_%s_IMG_%s_SSC_0';
		$urlNamePattern = 'PAGE_PARAS_%s_IMG_%s_URL_0';

		for($i = 0; $i < $count; $i++) {
			if(!($i % self::$imagesPerRow)) {
				$r .= '<tr>';
			}

			$src = $elementValues[$i]['SRC'][0];
			$url = $elementValues[$i]['URL'][0];
			$ssc = $elementValues[$i]['SSC'][0];

			$srcName = sprintf($srcNamePattern, $paramNum, $i);
			$sscName = sprintf($sscNamePattern, $paramNum, $i);
			$urlName = sprintf($urlNamePattern, $paramNum, $i);
			$propertiesUrl = 'Ink-XMS/panel.php?'
				. http_build_query(
					array(
						'id' => 'imageProperties.phtml',
						'title' => 'Eigenschappen',
						'srcName' => $srcName,
						'urlName' => $urlName,
					)
				);

			$r .= '<td class="block img-block" width="'.$widthPct.'%">'
				.self::_editBox(
					'<input name="'.$srcName.'" type="hidden" value="'.$src.'" />'
					.'<input name="'.$urlName.'" type="hidden" value="'.$url.'" />'
					.'<img alt="" name="'.$srcName.'" src="img.php?id='.$src.'" /><br />'
					.'<input align="center" class="ssc" name="'.$sscName.'" size="20" style="margin:5px 0px 0px 0px;" type="text" value="'.$ssc.'" />',
					array($srcName, $sscName, $urlName),
					$propertiesUrl
				)
				.'</td>';

			// if(the last img has been displayed, there is more than one row to
			// display and a row hasn't just been filled)
			if($i + 1 == $count && $count > self::$imagesPerRow && $count % self::$imagesPerRow != 0) {
				for($j = 0; $j < (self::$imagesPerRow - ($count % self::$imagesPerRow)); $j++) {
					$r .= '<td><br /></td>';
					$i++;
				}
			}

			if($i+1 == $count || (self::$imagesPerRow - ($i % self::$imagesPerRow)) == 1) {
				$r .= '</tr>';
			}
		}

		$name = 'PAGE_PARAS_'.$paramNum.'_IMG_'.$count;
		$colspan = $count < self::$imagesPerRow ? $count : self::$imagesPerRow;
		$newFields = array(
			sprintf($srcNamePattern, $paramNum, $count),
			sprintf($sscNamePattern, $paramNum, $count),
			sprintf($urlNamePattern, $paramNum, $count),
		);
		return $r.'<tr><td colspan="'.$colspan.'">'
			.'<div style="text-align:right;">'
			.'<a href="#" onclick="javascript:addInputs(this,[\''.implode('\',\'', $newFields).'\']);">Add image &gt;</a>'
			.'</div></td>';
	}

	protected function _displayPARA($paramNum, $elementValues) {
		$count = count($elementValues);
		$widthPct = (int)(100 / $count);
		$widthPx = self::_getElementWidthPixels($count);
		$r = '<tr>';
		$namePattern = 'PAGE_PARAS_%s_PARA_%s';

		for($i = 0; $i < $count; $i++) {
			// obtain all sentences
			$sentences = explode("\n", $elementValues[$i]);
			$rows = 0;

			// count how many rows the sentence will span
			foreach($sentences as $sentence) {
				$rows += max(1, ceil((strlen($sentence) * 7) / $widthPx));
			}

			$rows = max((int)$rows, 4);
			$name = sprintf($namePattern, $paramNum, $i);
			$r .= '<td class="block para-block" width="'.$widthPct.'%">'
				.self::_editBox(
					'<textarea name="'.$name.'" rows="'.$rows.'" style="width:'.$widthPx.'px">'.$elementValues[$i].'</textarea>',
					array($name)
				)
				.'</td>';
		}

		$newFields = array(sprintf($namePattern, $paramNum, $count));
		return $r.'</tr><tr><td colspan="'.$count.'">'
			.'<div style="text-align:right;">'
			.'<a href="#" onclick="javascript:addInputs(this,[\''.implode('\',\'', $newFields).'\']);">Add text &gt;</a>'
			.'</div></td>';
	}

	protected function _displayTITLE($paramNum, $elementValues) {
		$r = '<tr>';
		$count = count($elementValues);
		$widthPct = (int)(100 / $count);
		$width = self::_getElementWidthPixels($count);

		for($i = 0; $i < $count; $i++) {
			$name = 'PAGE_PARAS_'.$paramNum.'_TITLE_'.$i;
			$r .= '<td class="block title-block" width="'.$widthPct.'%">'
				.self::_editBox(
					'<input class="title" name="'.$name.'" style="width:'.$width.'px" type="text" value="'.$elementValues[$i].'" />',
					array($name)
				)
				.'</td>';
		}

		return $r.'</tr>';
	}

	/**
	 * Subfunctions for BackEnd().
	 * Take a variable amount of arguments.
	 */
	private static function _editBox($content, $fields, $propsUrl = NULL) {
		return '<table style="border: 1px dashed black; margin: 0 auto"><tr>'
			.'<td class="box-header" style="padding-bottom: 0; text-align: right;">'
			.(
				!is_null($propsUrl)
				? '<a href="#" onclick="javascript:showPanel(\''.$propsUrl.'\', 330, 360); return false;" title="Properties">'
				.'<img alt="Properties" src="Ink-XMS/img/o.gif" style="padding-left:2px;" /></a>'
				: ''
			)
			.'<a href="#" onclick="disableFields(this.parentNode.parentNode.parentNode, \''.implode('\',\'', $fields).'\'); return false;" title="Remove">'
			.'<img alt="Verwijder veld" src="Ink-XMS/img/x.gif" style="padding-left:2px;" /></a><br />'
			.'</td></tr><tr><td class="box-content">'.$content.'</td></tr></table>';
	}

	/**
	 * Check to see if a string is valid XML.
	 * @param string $str
	 * @return boolean
	 */
	private static function _isValidXml($str) {
		try {
			if(FALSE === simplexml_load_string('<test>'.$str.'</test>')) {
				return FALSE;
			}
			else {
				return TRUE;
			}
		}
		catch(ErrorException $e) {
			return FALSE;
		}
	}

	private static function _writeData() {
		//array_walk($_POST, 'walk_utf8_decode');
		//array_walk($_POST, 'walkhtmlnumericentities');
		array_walk($_POST, 'walknl2br');
		$tree = array();

		foreach($_POST as $inputName => $inputValue) {
			if(substr_count($inputName, '_') <= 1) {
				continue;
			}

			if(!self::_isValidXml($inputValue)) {
				return 'Invalid HTML given in '.$inputName.' '.$inputValue.'. Changes not saved.';
			}

			eval(
				'$tree[\''
				.str_replace('_', '\'][\'', $inputName)
				.'\'] = $inputValue;'
			);
		}

		require_once('XmlConversion.php');
		$xmlContent = InkXMS_XmlConversion::getDocumentXml($tree);
		$reply = InkXMS_Database::query(
			'UPDATE `'.InkXMS_Config::$space.'_page` SET `content`=\''.InkXMS_Database::escape($xmlContent).'\', `updated`=NOW() WHERE `title`=\''.mysql_escape_string(InkXMS_Page::$title).'\' AND `language`=\''.InkXMS_Database::escape(InkXMS_Page::$language).'\''
		);

		return mysql_affected_rows() > 0 ? 'The changes have been published successfully.' : 'Nothing has been changed.';
	}
}