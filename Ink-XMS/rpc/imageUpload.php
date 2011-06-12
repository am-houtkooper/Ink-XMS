<?php
if(isset($_GET['button'])) {
	$_GET['button'] = addslashes($_GET['button']);
}

if(isset($_GET['field'])) {
	$_GET['field'] = addslashes($_GET['field']);
}

$message = 'Upload not successful';
$newName = 'NONE';
$id = 'NONE';
$success = false;

if(
	isset($_GET['field'])
	&& is_uploaded_file($_FILES[$_GET['field']]['tmp_name'])
) {
	$tmpImageName = $_FILES[$_GET['field']]['tmp_name'];
	$imageSize = $_FILES[$_GET['field']]['size'];

	if($imageSize >= 2000000) {
		$message = 'File exceeds the maximum filesize limit.';
	}
	else {
		$imgData = addslashes(file_get_contents($tmpImageName));
		$size = getimagesize($tmpImageName);
		InkXMS_Database::query(
			'INSERT INTO `'
			.InkXMS_Config::$space
			.'_photo` (`name`, `type` ,`image`, `size`, `height`, `width`)'
			.'VALUES (\''
			.$_FILES[$_GET['field']]['name']
			.'\', \''
			.$size['mime']
			.'\', \''
			.$imgData
			.'\', \''
			.$imageSize
			.'\', \''
			.$size[1]
			.'\', \''
			.$size[0]
			.'\')'
		);
		$message = 'Upload succesful.';
		$newName = $_FILES[$_GET['field']]['name'];
		$id = mysql_insert_id();
		$success = true;
	}
}
?>
<script language="JavaScript">
	// window.parent is a read only reference! So call the reply function on
	// that reference, the function can then work on it.
	window.parent.remoteImageUploadReply(
		'<?= $success ? 'true':'false' ?>',
		'<?= $message ?>',
		'<?= $_GET['button'] ?>',
		'<?= $_GET['field'] ?>',
		'<?= $newName ?>',
		'<?= $id ?>'
	);
</script>