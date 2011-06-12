<?php
/**
 * Returns this url including all of it's GET variables, with:
 * $amend a list of variables with new values, if the values is null, the
 * field doesn't have a value and the '=' sign is skipped.
 * $delete contains all the fields that should be removed
 */
function thisURL($amends = array('amend' => array(), 'delete' => array())) {
	global $_SERVER, $_GET;

	if(!array_key_exists('amend', $amends))
		$amends['amend'] = array();
	else if(is_string($amends['amend']))
		$amends['amend'] = array($amends['amend'] => null);

	if(!array_key_exists('delete', $amends)) {
		$amends['delete'] = array();
	} else if(is_string($amends['delete'])) {
		$amends['delete'] = array($amends['delete']);
	}

	$args = array_merge($_GET, $amends['amend']);
	$names = array_diff(array_keys($args), $amends['delete']);
	$r = $_SERVER['PHP_SELF'].(count($names)>0 ? '?':'');

	foreach($names as $name) {
		if(is_null($args[$name]) && $args[$name] !== 0) {
			continue;
		}

		$r .= $name.($args[$name] === '' ? '' : '='.$args[$name]).'&';
	}

	return substr($r, 0, -1);
}


function walkbr2nl(&$str, $new) {
	$str = preg_replace('/(<|&lt;)br\s?\/?(>|&gt;)(\n|\r)?/', PHP_EOL, $str);
}

function walknl2br(&$str, $new) {
	$str = nl2br($str);
}

function walkhtmlspecialchars(&$str, $key) {
	$str = htmlspecialchars($str);
}

function walk_utf8_decode(&$str, $key) {
	$str = utf8_decode($str);
}

function walkhtmlentities(&$str, $key) {
	$str = htmlentitiesNoDoubleEncode($str);
}
function htmlentitiesNoDoubleEncode($myHTML) {
	$trans = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);
	$trans[chr(38)] = '&';
	return preg_replace(
		'/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,4};)/',
		'&amp;',
		strtr($myHTML, $trans)
	);
}

function walkhtmlentitiesdecode(&$str, $key) {
   $str = strtr($str, array_flip(get_html_translation_table(HTML_ENTITIES)));
}

// Numeric entities functions - for ISO-8859-1 compatible XML
// based upon code by ryan at ryancannon dot com -
// http://nl3.php.net/manual/en/function.get-html-translation-table.php
function htmlnumericentities($str, $trans='') {
	$trans = is_array($trans)
		? $trans
		: get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);

	foreach($trans as $k => $v) {
		$trans[$k]= '&#'.ord($k).';';
	}

	return strtr($str, $trans);
}
function walkhtmlnumericentities(&$str, $key) {
	$str = walkhtmlnumericentitiesNoDoubleEncode($str);
}
function walkhtmlnumericentitiesNoDoubleEncode($str) {
	$trans = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);

	foreach($trans as $k => $v) {
		$trans[$k]= '&#'.ord($k).';';
	}

	$trans[chr(38)] = '&';
	return preg_replace(
		'/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,4};)/',
		'&amp;',
		strtr($str, $trans)
	);
}
function htmlnumericentitiesdecode($str, $trans='') {
	$trans = is_array($trans)
		? $trans
		: get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);

	foreach($trans as $k => $v) {
		$trans[$k]= '&#'.ord($k).';';
	}

	return strtr($str, array_flip($trans));
}

// http://uk.php.net/manual/en/function.array-diff-assoc.php - jochem at iamjochem dawt com (enhanced version of function by Michael Johnson)
function array_diff_keys() {
	$args = func_get_args();
	$r = $args[0];

	for($i = 1; $i < count($args); $i++) {
		foreach($args[$i] as $key => $value) {
			unset($r[$key]);
		}
	}

	return $r;
}

// Built in in PHP5 as array_walk_recursive, takes extra user arguments for
// $function - just like array_walk
// NOTE: Can't use a foreach because you wouldn't use a reference to the value
// anymore, hence the original array wouldn't get updated.
function array_walk_recurs(&$array, $function) {
	$keys = array_keys($array);
	$count = count($keys);

	for($i = 0; $i < $count; $i++) {
		if(is_array($array[$keys[$i]])) {
			array_walk_recurs($array[$keys[$i]], $function);
		}
		else {
			$args = func_get_args();
			array_splice($args, 0, 2);
			$code = $function.'($array[$keys[$i]], $keys[$i]'.(count($args)>0 ? ', ' : '').implode(', ', $args).');';
			eval($code);
		}
	}
}
?>
