/*
	Online Course Resources System [Pre-Clerkship]
	Developed By:	Medical Education Technology Unit
	Director:		Dr. Benjamin Chen <bhc@post.queensu.ca>
	Developers:	Matt Simpson <simpson@post.queensu.ca>

	$Id: picklist-faculty.js 639 2009-08-18 17:10:04Z hbrundage $

	Original Copyright Heading:	
	===============================================
	This is based on _picklist II script - By Phil Webb (http://www.philwebb.com)
	Visit JavaScript Kit (http://www.javascriptkit.com) for this JavaScript and
	100s more. Please keep this notice intact.
*/

function init_picklist(fieldName) {
	if (!fieldName) {
		fieldName = '';
	}
	
	if ($(fieldName + '_picklist')) {
		picklist_toggle_remove_button(fieldName);
		
		if (fieldName == 'faculty') {
			picklist_highlight_first_entry('faculty');
		}
	}
}

function picklist_add(fieldName) {
	if (!fieldName) {
		fieldName = '';
	}
	
	if ($(fieldName + '_selectlist') && $(fieldName + '_picklist')) {
		picklist_move(fieldName, $(fieldName + '_selectlist'), $(fieldName + '_picklist'));
	}
}

function picklist_del(fieldName) {
	if (!fieldName) {
		fieldName = '';
	}

	if ($(fieldName + '_selectlist') && $(fieldName + '_picklist')) {
		picklist_move(fieldName, $(fieldName + '_picklist'), $(fieldName + '_selectlist'), fieldName);
	}
}

function picklist_move(fieldName, fbox, tbox) {
	var arrFbox		= new Array();
	var arrTbox		= new Array();
	var arrLookup	= new Array();
	var i;

	if(!fieldName) {
		fieldName = '';
	}
     
	for(i = 0; i < tbox.options.length; i++) {
		arrLookup[tbox.options[i].text] = tbox.options[i].value;
		arrTbox[i] = tbox.options[i].text;
	}
	
	var fLength	= 0;
	var tLength	= arrTbox.length;
	
	for(i = 0; i < fbox.options.length; i++) {
		arrLookup[fbox.options[i].text] = fbox.options[i].value;
		if(fbox.options[i].selected && fbox.options[i].value != "") {
			arrTbox[tLength] = fbox.options[i].text;
			tLength++;
		} else {
			arrFbox[fLength] = fbox.options[i].text;
			fLength++;
		}
	}
	
	if (tbox.id == fieldName + '_selectlist') {
		arrTbox.sort();
	}
	
	fbox.length	= 0;
	tbox.length	= 0;
	
	var c;
	
	for(c = 0; c < arrFbox.length; c++) {
		var no		= new Option();
		no.value	= arrLookup[arrFbox[c]];
		no.text		= arrFbox[c];
		fbox[c]		= no;
	}
	
	for(c = 0; c < arrTbox.length; c++) {
		var no		= new Option();
		no.value	= arrLookup[arrTbox[c]];
		no.text		= arrTbox[c];	
		tbox[c]		= no;
	}
	
	if (fieldName == 'faculty') {
		picklist_highlight_first_entry('faculty');
		picklist_toggle_reorder_element('faculty');
	}
	
	if ($(fieldName + '_picklist')) {
		picklist_toggle_remove_button(fieldName);
	}
}

function picklist_moveup(fieldName) {
	if (!fieldName) {
		fieldName = '';
	}

	var selectList		= $(fieldName+'_picklist');
	var selectOptions	= selectList.getElementsByTagName('option');
	
	for (var i = 1; i < selectOptions.length; i++) {
		var opt = selectOptions[i];
		if (opt.selected) {
			selectList.removeChild(opt);
			selectList.insertBefore(opt, selectOptions[i - 1]);
		}
	}

	if (fieldName == 'faculty') {
		picklist_highlight_first_entry('faculty');
	}
}

function picklist_movedown(fieldName) {
	if (!fieldName) {
		fieldName = '';
	}

	var selectList		= $(fieldName+'_picklist');
	var selectOptions	= selectList.getElementsByTagName('option');
	
	for (var i = selectOptions.length - 2; i >= 0; i--) {
		var opt = selectOptions[i];
		if (opt.selected) {
			var nextOpt = selectOptions[i + 1];
			opt = selectList.removeChild(opt);
			nextOpt = selectList.replaceChild(opt, nextOpt);
			selectList.insertBefore(nextOpt, opt);
		}
	}
	
	if (fieldName == 'faculty') {
		picklist_highlight_first_entry('faculty');
	}
}

function picklist_select(fieldName) {
	if (!fieldName) {
		fieldName = '';
	}
	
	if ($(fieldName + '_picklist')) {
		var pickList = $(fieldName + '_picklist');
		var pickOptions = pickList.options;
		var pickOLength = pickOptions.length;
		
		for (var i = 0; i < pickOLength; i++) {
			pickOptions[i].selected = true;
		}
	}
		
	return true;
}

function picklist_highlight_first_entry(fieldName) {
	if (!fieldName) {
		fieldName = '';
	}

	if ($(fieldName + '_picklist')) {
		var pickList = $(fieldName + '_picklist');
		var pickOptions = pickList.options;
		var pickOLength = pickOptions.length;
		
		if (pickOLength > 1) {
			for (var i = 0; i < pickOLength; i++) {
				if ($(pickOptions[i]).hasClassName('first')) {
					$(pickOptions[i]).removeClassName('first');
				}
			}
		}
		
		if (pickOptions[0]) {
			$(pickOptions[0]).addClassName('first');
		}
	}
}

function picklist_toggle_reorder_element(fieldName, index) {
	if(!fieldName) {
		fieldName = '';
	}
	
	if ($(fieldName + '_picklist')) {
		if (((index) || (index === 0)) && (index != undefined) && (index != '-1')) {
			if ($(fieldName + '_picklist').options.length > 1) {
				$(fieldName + '_list_options_reorder').appear({
					duration: 0.3
				});
			}
		} else {
			picklist_close_reorder_element(fieldName);
		}
	}
}

function picklist_close_reorder_element(fieldName) {
	if (!fieldName) {
		fieldName = '';
	}
	
	if ($(fieldName + '_list_options_reorder')) {
		$(fieldName + '_list_options_reorder').fade({
			duration: 0.3
		});
	}
	
	if ($(fieldName + '_picklist')) {
		for (i = 0; i < $(fieldName + '_picklist').options.length; i++) {
			$(fieldName + '_picklist').options[i].selected = false;
		}
	}
}

function picklist_toggle_remove_button(fieldName) {
	if (!fieldName) {
		fieldName = '';
	}

	if ($(fieldName + '_picklist')) {
		if ($(fieldName + '_picklist').options.length > 0) {
			$(fieldName + '_list_options_remove_btn').appear({
				duration: 0.3
			});
		} else {
			$(fieldName + '_list_options_remove_btn').fade({
				duration: 0.3
			});
		}
	}
	
}