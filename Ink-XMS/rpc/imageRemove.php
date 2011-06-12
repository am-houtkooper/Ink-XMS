<?php
if(array_key_exists('button', $_GET)) {
	$_GET['button'] = addslashes($_GET['button']);
}

if(array_key_exists('field', $_GET)) {
	$_GET['field'] = addslashes($_GET['field']);
}

$message = '';
$id = 'NONE';
$success = false;

if(
	array_key_exists('field', $_GET)
	&& array_key_exists($_GET['field'], $_POST)
	&& is_numeric($_POST[$_GET['field']])
) {
	$id = $_POST[$_GET['field']];
	InkXMS_Database::query(
		'DELETE FROM `'.InkXMS_Config::$space.'_photo` WHERE `id` = '.$id
	);
	$message = 'Removal succesful.';
	$success = true;
}
?>
<script language="JavaScript">
	// window.parent is a read only reference! So call the reply function on
	// that reference, the function can then work on it.
	window.parent.remoteImageRemoveReply(
		'<?= $success ? 'true':'false' ?>',
		'<?= $message ?>',
		'<?= $_GET['button'] ?>',
		'<?= $_GET['field'] ?>',
		'<?= $id ?>'
	);
</script>