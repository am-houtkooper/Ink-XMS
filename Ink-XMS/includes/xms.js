function addInputs(anchor, fieldNames) {
	for(var i = 0; i < fieldNames.length; i++) {
		var input = document.createElement('input');
		input.setAttribute('type', 'hidden');
		input.setAttribute('value', '');
		input.setAttribute('name', fieldNames[i]);
		anchor.appendChild(input);
	}

	submitForm();
}

function showPanel(url, width, height) {
	window.open(
		url,
		'panel',
		'toolbar=1,location=0,directories=0,status=1,menubar=0,scrollbars=1,resizable=1,width='+width+',height='+height
	).focus();
}

/**
 * Doesn't work in Netscape 7.02, does in IE 5.5 and Opera 7.02
 */
function disableFields(box, fieldNames) {
	if(box.parentNode.parentNode.parentNode.childNodes.length == 1) {
		box.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.removeChild(box.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode);
	}
	else {
		box.parentNode.parentNode.parentNode.removeChild(box.parentNode.parentNode);
	}
	
	for(var i = 1; i < fieldNames.length; i++) {
		document.forms['interface'][fieldNames[i]].disabled = true;
	}
}

function submitForm() {
	var form = document.forms['interface'];
	form.submit();
}

function extractGET() {
	var result = {};
	var address = (String)(window.location);
	var tmp = address.split('?');
	tmp = tmp[1];
	tmp = tmp.split('&');

	for(var i = 0; i < tmp.length; i++) {
		var tmp2 = tmp[i].split('=');
		result[tmp2[0]] = tmp2[1];
	}

	return result;
}

// properties functions
//// imageProperties
function toggleChooseImage(input) {
	var chooseImagePane = document.getElementById('fotoList');
	
	if(chooseImagePane.style.display != 'none') {
		// remove it
		//chooseImagePane = input.parentNode.parentNode.parentNode.removeChild(input.parentNode.parentNode.nextSibling);
		chooseImagePane.style.display = 'none';
		input.value = 'Change';
	}
	else {
		// introduce it
		//input.parentNode.parentNode.parentNode.insertBefore(chooseImagePane.cloneNode(true), input.parentNode.parentNode.nextSibling);
		chooseImagePane.style.display = '';

		// select the image that is in the Image field, if possible
		var selIndex = -1;

		for(var k = 0; k < document.forms['panel'].imageChoice.options.length; k++) {
			if(document.forms['panel'].imageChoice.options[k].text == document.forms['panel'].Image.value) {
				selIndex = k;
				break;
			}
		}
		
		document.forms['panel'].imageChoice.selectedIndex = selIndex;
		input.value = 'Cancel';
	}
}

function remoteImageRemoveQuery(a, fieldName) {
	var sel = document.forms['panel'].elements[fieldName];
	var delImg = sel.options[sel.selectedIndex];

	if(confirm('Weet u zeker dat u \''+delImg.text+'\' wilt verwijderen?')) {
		a.value = 'Removing...';
		var console = document.getElementById('panelConsole');
		console.innerHTML = '';
		document.forms['panel'].action = 'RPC.php?id=imageRemove.php&button='+a.name+'&field='+fieldName;
		document.forms['panel'].submit();
		a.disabled = true;
		sel.disabled = true;
	}
}

function remoteImageRemoveReply(
	success, message, buttonName, fieldName, removedId
) {
	var console = document.getElementById('panelConsole');
	console.innerHTML = message+'<br />';
	var button = document.forms['panel'].elements[buttonName];
	button.value = 'Remove';
	button.disabled = false;
	var sel = document.forms['panel'].elements['imageChoice'];
	sel.disabled = false;
	
	if(success) {
		var oldText;

		// remove the deleted image from the list
		for(var i = 0; i < sel.options.length; i++) {
			if(sel.options[i].value == removedId) {
				oldText = sel.options[i].text;
				sel.options[i].parentNode.removeChild(sel.options[i]);
				break;
			}
		}

		sel.selectedIndex = -1;

		// change the image in the text field, if it was the deleted one
		if(document.forms['panel'].elements['Image'].value == oldText) {
			document.forms['panel'].Image.value = '...';
		}
	}
}

function remoteImageUploadQuery(a, fieldName) {
	a.value = 'Loading...';
	var console = document.getElementById('panelConsole');
	console.innerHTML = '';
	document.forms['panel'].action = 'RPC.php?id=imageUpload.php&button='+a.name+'&field='+fieldName;
	document.forms['panel'].submit();
	a.disabled = true;
	document.forms['panel'].elements[fieldName].disabled = true;
}

function remoteImageUploadReply(
	success, reply, buttonName, fieldName, newText, newValue
) {
	var console = document.getElementById('panelConsole');
	console.innerHTML = reply+'<br />';
	var button = document.forms['panel'].elements[buttonName];
	button.value = 'Upload';
	button.disabled = false;
	var field = document.forms['panel'].elements[fieldName];
	field.disabled = false;
	field.parentNode.replaceChild(field.cloneNode(true), field);

	if(success) {
		var sel = document.forms['panel'].elements['imageChoice'];
		sel.options[sel.options.length] = new Option(newText, newValue, false, true);
		sel.options[sel.options.length-1].selected = true;
	}
}

function chooseImage() {
	if(document.forms['panel'].imageChoice.selectedIndex != -1) {
		document.forms['panel'].Image.value = document.forms['panel'].imageChoice.options[document.forms['panel'].imageChoice.selectedIndex].text;
	}
	
	toggleChooseImage(document.forms['panel'].TCI);
}

function initImageProperties() {
	// first load the data over from the top.opener
	var get = extractGET();
	var srcField = eval('top.opener.document.forms[\'interface\'].'+get['srcName']+';');
	var urlField = eval('top.opener.document.forms[\'interface\'].'+get['urlName']+';');
	
	for(var k = 0; k < document.forms['panel'].imageChoice.options.length; k++) {
		if(document.forms['panel'].imageChoice.options[k].value == srcField.value) {
			document.forms['panel'].Image.value = document.forms['panel'].imageChoice.options[k].text;
			break;
		}
	}

	document.forms['panel'].Link.value = urlField.value;
	toggleChooseImage(document.forms['panel'].TCI);
}

function submitImageProperties() {
	var get = extractGET();
	var srcField = eval('top.opener.document.forms[\'interface\'].'+get['srcName']+';');
	var urlField = eval('top.opener.document.forms[\'interface\'].'+get['urlName']+';');
	var chooseImagePane = document.getElementById('fotoList');
	var imageChoice = null;
	
	if(chooseImagePane == null) {
		imageChoice = document.forms['panel'].imageChoice;
	}
	else {
		imageChoice = getChildNodesByName(chooseImagePane, 'imageChoice')[0];
	}
	for(var k = 0; k < imageChoice.options.length; k++) {
		if(imageChoice.options[k].text == document.forms['panel'].Image.value) {
			srcField.value = imageChoice.options[k].value;
			break;
		}
	}

	image = eval('top.opener.document.'+get['srcName']);
	image.src = 'img.php?id='+srcField.value;
	urlField.value = document.forms['panel'].Link.value;
	window.close();
}
//// end imageProperties
// end properties

function getChildNodesByName(parentNode, childNodeName) {
	var result = new Array();
	
	for(var i = 0; i < parentNode.childNodes.length; i++) {
		if(parentNode.childNodes[i].name != undefined && parentNode.childNodes[i].name == childNodeName) {
			result = result.concat(parentNode.childNodes[i]);
		}
		
		result = result.concat(getChildNodesByName(parentNode.childNodes[i], childNodeName));
	}
	
	return result;
}
