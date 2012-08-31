var wizardStep	= 1;
var maxSteps	= 3;

function allowSubmit(checked) {
	if (checked) {
		$('next-button').enable();
	} else {
		$('next-button').disable();
	}
}

function parentReload() {
	if (window.opener && !window.opener.closed) {
		window.opener.location.href = window.opener.location.href;
	}
}

function closeWizard() {
	window.close();

	if (window.opener && !window.opener.closed) {
		window.opener.focus();
	}
}

function updateTitle() {
	if($('step-title') != null) {
		$('step-title').innerHTML = 'Step '+wizardStep+' of '+maxSteps;
	}

	return true;
}

function updateButtons() {
	if (wizardStep == 1) {
		$('back-button').style.display = 'none';
	} else if (wizardStep > 1) {
		$('back-button').style.display = 'inline';
	}

	if (wizardStep == maxSteps) {
		$('next-button').value = 'Finish';
	} else {
		$('next-button').value = 'Next Step';
	}

	return true;

}

function initStep(step) {
	if((step) && (step >= 1) && (step <= maxSteps)) {
		wizardStep = step;
	}

	if (wizardStep != 1) {
		$('step1').style.display		= 'none';
	}

	$('step' + wizardStep).style.display	= 'block';

	updateButtons();
	updateTitle();

	return true;
}

function prevStep() {
	if (wizardStep > 1) {
		$('step' + wizardStep).style.display	= 'none';
		wizardStep = wizardStep - 1;
		$('step' + wizardStep).style.display	= 'block';
	}

	updateButtons();
	updateTitle();

	return true;
}

function nextStep() {
	if (wizardStep < maxSteps) {
		$('step' + wizardStep).style.display	= 'none';
		wizardStep = wizardStep + 1;
		$('step' + wizardStep).style.display	= 'block';

		updateButtons();
		updateTitle();
	} else {
		$('uploading-window').style.display	= 'block';

		$('wizard-form').submit();
	}

	return true;
}

function handleKeys(event) {
	var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;

	// Enter key goes to next step.
	if (keyCode == 13) {
		nextStep();
	}

	return false;
 }

function grabFilename() {
	var fn = $("filename").value;
	if (fn == ''){
		$("file_title").value = '';
	} else {
		if($("file_title").value == '') {
			var filename = fn.match(/[\/|\\]([^\\\/]+)$/);

			if (filename == null) {
				filename = fn; // Opera
			} else {
				filename = filename[1];
			}

			$("file_title").value = filename;
		}

		$("file_title").focus();
	}
}

function timedRelease(state) {
	if($('timed-release-info') != null) {
		$('timed-release-info').style.display = state;
	}

	return true;
}

function updateFile(state) {
	if($('upload-new-file') != null) {
		$('upload-new-file').style.display = state;
	}

	return true;
}