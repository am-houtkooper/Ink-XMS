<?php
class InkXMS_XmlConversion {
	/**
	 * Convert DOM to array structure.
	 * @param string $xmlData
	 * @return array
	 */
	public static function getTree($xmlData) {
		$r = array();
		$document = new DOMDocument();
		$document->loadXML($xmlData);

		foreach($document->childNodes->item(0)->childNodes as $parasNode) {
			$blocks = array();

			foreach($parasNode->childNodes as $blockNode) {
				if(in_array($blockNode->tagName, array('IMG', 'CELLROW'))) {
					$props = array();

					foreach ($blockNode->childNodes as $propNode) {
						$props[$propNode->tagName][] = $propNode->textContent;
					}

					$blocks[$blockNode->tagName][] = $props;
				}
				else {
					$blocks[$blockNode->tagName][] = substr(
						$blockNode->ownerDocument->saveXML($blockNode ),
						strlen($blockNode->tagName) + 2,
						- strlen($blockNode->tagName) - 3
					);
				}
			}

			$r[$parasNode->tagName][] = $blocks;
		}

		return $r;
	}

	/**
	 * Generate the XML document of an assoc data array.
	 * @param array $data
	 * @return string
	 */
	public static function getDocumentXml(array $tree) {
		reset($tree);
		$rootElementName = key($tree);
		return '<?xml version="1.0" encoding="UTF-8"?><'.$rootElementName.'>'
			.self::_getChildXml($tree[$rootElementName])
			.'</'.$rootElementName.'>';
	}

	/**
	 * Generate the child XML of an assoc data array.
	 * @param array $tree
	 * @return string
	 */
	private static function _getChildXml(array $tree) {
		$r = '';

		foreach($tree as $type => $values) {
			foreach($values as $value) {
				$r .= '<'.$type.'>'
					.(is_array($value) ? self::_getChildXml($value) : $value)
					.'</'.$type.'>';
			}
		}

		return $r;
	}

	function PIHandler($parser, $target, $data) {
		if(strtolower($target) == "php") {
			global $parser_file;
			echo eval($data);
		}
	}
}
