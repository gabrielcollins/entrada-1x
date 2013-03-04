function toggle_list(element_id) {
	if($(element_id).style.display == 'none') {
		new Effect.BlindDown($(element_id), { duration: 0.3 });

		$(element_id+'_state_btn').addClassName('button-red');
		$(element_id+'_state_btn').value = 'Hide List';

		$(element_id+'_add_btn').appear({ duration: 0.3 });
	} else {
		new Effect.BlindUp($(element_id), { duration: 0.3 });

		$(element_id+'_state_btn').removeClassName('button-red');
		$(element_id+'_state_btn').value = ('Show List');

		$(element_id+'_add_btn').fade({ duration: 0.3 });
	}
}

function toggle_visibility_checkbox(obj, element_id, effect) {
	if((!effect) || (effect != 'blind')) {
		effect = 'fade';
	}

	if($(element_id) != null) {
		if(obj.checked == true) {
			switch(effect) {
				case 'fade' :
					Effect.Appear(element_id);
				break;
				case 'blind' :
					Effect.BlindDown(element_id);
				break;
				default :
					$(element_id).style.display	= '';
				break;
			}
		} else {
			switch(effect) {
				case 'fade' :
					Effect.Fade(element_id);
				break;
				case 'blind' :
					Effect.BlindUp(element_id);
				break;
				default :
					$(element_id).style.display	= 'none';
				break;
			}
		}
	}
	return;
}

function toggle_visibility(element_id, effect) {
	if($(element_id) != null) {
		if($(element_id).style.display == 'none') {
			switch(effect) {
				case 'fade' :
					Effect.Appear(element_id);
				break;
				case 'blind' :
					Effect.BlindDown(element_id);
				break;
				default :
					$(element_id).style.display	= '';
				break;
			}
		} else {
			switch(effect) {
				case 'fade' :
					Effect.Fade(element_id);
				break;
				case 'blind' :
					Effect.BlindUp(element_id);
				break;
				default :
					$(element_id).style.display	= 'none';
				break;
			}
		}
	}
	return;
}

function updateTime(type) {
	var hour	= $F(type+'_hour');
	var minute	= $F(type+'_min');
	var suffix	= '';

	// If it's not past 12 don't bother.
	if(hour >= 12) {
		hour	= hour % 12;
		suffix	= 'PM';
	} else {
		suffix	= 'AM';
	}

	// Crude adjustments for silly 12 hour format.
	if(parseInt(hour) == 0) {
		hour = '12';
	}
	// Crude adjustments for the zeros.
	if(parseInt(minute) == 0) {
		minute = '00';
	}

	$(type+'_display').innerHTML = hour+':'+minute+' '+suffix;

	return;
}

function dateLock(field) {
	if($(field) && $(field).checked == true) {
		$(field+'_text').className	= 'form-required';
		$(field+'_date').disabled	= false;
		if($(field+'_hour') != null) {
			$(field+'_hour').disabled = false;
		}
		if($(field+'_min') != null) {
			$(field+'_min').disabled = false;
		}
	} else {
		$(field+'_text').className	= 'form-nrequired';
		$(field+'_date').disabled	= true;
		if($(field+'_hour') != null) {
			$(field+'_hour').disabled = true;
		}
		if($(field+'_min') != null) {
			$(field+'_min').disabled = true;
		}
	}
	return;
}

function upload() {
	$('addbutton').disabled		= true;
	$('addbutton').style.color	= '#666666';
	$('status').innerHTML		= 'Please wait. Uploading data to server ...';

	document.forms[0].submit();
}

function customConfig(config) {
	config.toolbar = [
		[ "bold", "italic", "underline", "separator",
		  "orderedlist", "unorderedlist", "outdent", "indent", "separator",
		  "htmlmode", "popupeditor"
		]
	];
	config.pageStyle	= 'body { font-family: Verdana, Arial, sans-serif; font-size: 12px; margin: 5px }';
	config.statusBar	= false;
}

function getSelectedButton(buttonGroup) {
	for (var i = 0; i < buttonGroup.length; i++) {
		if (buttonGroup[i].checked) {
			return i;
		}
	}
	return -1; //no button selected
}

function sendFeedback(url) {
	if(url) {
		var windowW = 485;
		var windowH = 585;

		var windowX = (screen.width / 2) - (windowW / 2);
		var windowY = (screen.height / 2) - (windowH / 2);

		feedbackWindow = window.open(url, 'feedbackWindow', 'width='+windowW+', height='+windowH+', scrollbars=yes');
		feedbackWindow.blur();
		window.focus();

		feedbackWindow.resizeTo(windowW, windowH);
		feedbackWindow.moveTo(windowX, windowY);

		feedbackWindow.focus();
	}
	return;
}

function sendClerkship(url) {
	if(url) {
		var windowW = 485;
		var windowH = 585;

		var windowX = (screen.width / 2) - (windowW / 2);
		var windowY = (screen.height / 2) - (windowH / 2);

		clerkshipWindow = window.open(url, 'clerkshipWindow', 'width='+windowW+', height='+windowH+', scrollbars=yes');
		clerkshipWindow.blur();
		window.focus();

		clerkshipWindow.resizeTo(windowW, windowH);
		clerkshipWindow.moveTo(windowX, windowY);

		clerkshipWindow.focus();
	}
	return;
}

function sendAccommodation(url) {
	if(url) {
		var windowW = 485;
		var windowH = 585;

		var windowX = (screen.width / 2) - (windowW / 2);
		var windowY = (screen.height / 2) - (windowH / 2);

		accommodationWindow = window.open(url, 'accommodationWindow', 'width='+windowW+', height='+windowH+', scrollbars=yes');
		accommodationWindow.blur();
		window.focus();

		accommodationWindow.resizeTo(windowW, windowH);
		accommodationWindow.moveTo(windowX, windowY);

		accommodationWindow.focus();
	}
	return;
}

function closeWindow() {
	window.close();

	if (window.opener && !window.opener.closed) {
		window.opener.focus();
	}
}

function fieldCopy(copy_from, copy_to, copy_only_empty) {
	if((!copy_only_empty) || (copy_only_empty == null)) {
		copy_only_empty = 0;
	} else {
		copy_only_empty = 1;
	}

	if(((copy_only_empty) && (document.getElementById(copy_from) != null)) || (!copy_only_empty)) {
		if((!copy_only_empty) || ((copy_only_empty) && (document.getElementById(copy_to).value != ""))) {
		} else {
			document.getElementById(copy_to).value = document.getElementById(copy_from).value;
		}
	}

	return true;
}

function noPublic(obj) {
	obj.checked = false;
	alert('Non-Authenticated / Public Users cannot access this function at this time.');

	return;
}

function uploadPhoto() {
	if($('display-upload-button')) {
		if($('display-upload-status')) {
			if(($('photo_file')) && ($('photo_file').value != '')) {
				$('display-upload-button').innerHTML = $('display-upload-status').innerHTML;
			}
		}
	}

	if($('upload-photo-form')) {
		$('upload-photo-form').submit();
	}

	return;
}

function photoShow(url, width, height) {
	img = new Image(width, height);
	img.src = url;
	var win = new UI.Window(
	{
		shadow:	true,
		shadowTheme: "drop_shadow",
		theme: "alphacube",
		title: "User Photo",
		width: img.width + 4,
		height: img.height + 38,
		resizable: false
	}).center().setContent("<img src=\'"+url+"\' />").show();
}

function setMaxLength() {
	var x = document.getElementsByTagName('textarea');
	var counter = document.createElement('div');
	counter.className = 'content-small';
	for (var i=0;i<x.length;i++) {
		if (x[i].getAttribute('maxlength')) {
			var counterClone = counter.cloneNode(true);
			counterClone.relatedElement = x[i];
			counterClone.innerHTML = 'Character Count: <span>0</span>/'+x[i].getAttribute('maxlength');
			x[i].parentNode.insertBefore(counterClone,x[i].nextSibling);
			x[i].relatedElement = counterClone.getElementsByTagName('span')[0];

			x[i].onkeyup = x[i].onchange = checkMaxLength;
			x[i].onkeyup();
		}
	}
}

function checkMaxLength() {
	var maxLength = this.getAttribute('maxlength');
	var currentLength = this.value.length;
	if (currentLength > maxLength)
		this.relatedElement.className = 'content-red';
	else
		this.relatedElement.className = 'content-small';
	this.relatedElement.firstChild.nodeValue = currentLength;
}

var checkflag = 'false';
function selection(field) {
	if(checkflag == 'false') {
		if(!field.length) {
			field.checked = true;
		} else {
			for (i = 0; i < field.length; i++) {
				field[i].checked = true;
			}
		}
		checkflag = 'true';
		return;
	} else {
		if(!field.length) {
			field.checked = false;
		} else {
			for (i = 0; i < field.length; i++) {
				field[i].checked = false;
			}
		}
		checkflag = 'false';
		return;
	}
}

var ExpandableTextarea = Class.create({
	initialize: function(el) {
		this.textbox = { element: el, defaultheight: el.getHeight() }
		this.textbox.element.update = this.setTextboxHeight;
		this.createHiddenElement();
		this.setTextboxHeight(false);
		this.animate = (typeof Scriptaculous == 'undefined') ? false : true;
		this.textbox.element.setStyle({'overflow': 'hidden'});

		Event.observe(this.textbox.element, 'keyup', this.handleKeyUp.bind(this));
		Event.observe(this.textbox.element, 'focus', this.setTextboxHeight.bind(this));
	},

	createHiddenElement: function() {
		this.hiddenelement = new Element('div').show();

		// How do I get rid of this mess?
		this.hiddenelement.setStyle({
			'paddingTop': this.textbox.element.getStyle('paddingTop'),
			'paddingRight': this.textbox.element.getStyle('paddingRight'),
			'paddingBottom': this.textbox.element.getStyle('paddingBottom'),
			'paddingLeft': this.textbox.element.getStyle('paddingLeft'),
			'fontSize': this.textbox.element.getStyle('font-size'),
			'fontFamily': this.textbox.element.getStyle('font-family'),
			'width': this.textbox.element.getStyle('width'),
			'display': 'block',
			'visibility': 'hidden',
			'position': 'absolute',
			'top': '0',
			'left': '0'
		});

		this.textbox.element.parentNode.appendChild(this.hiddenelement);
	},

	handleKeyUp: function() {
		this.setTextboxHeight(this.animate);
	},

	setTextboxHeight: function(animate) {
		currenttextheight = this.hiddenelement.update(this.textbox.element.value.replace(/\n/g, '\n').replace(/<|>/g, ' ').replace(/\n/g, '<br />').replace(/&/g,"&amp;").replace(/  /g,' &nbsp;')).getHeight();
		goalheight = ((currenttextheight>this.textbox.defaultheight)?currenttextheight+20:this.textbox.defaultheight);

		if(animate)
			this.textbox.element.morph({ height: goalheight + 'px'}, { duration: 0.2 });
		else
			this.textbox.element.setStyle({ height: goalheight + 'px' });

	}
});

var CollapseHeadings = Class.create({
	initialize: function(el) {
		this.el = $(el);
		this.child = this.el.title.split(' ').join('-').toLowerCase();
		if (($(this.child)) && (this.el.hasClassName('nocollapse') == false)) {
			this.el.addClassName('collapsable');

			if (this.el.hasClassName('collapsed')) {
				$(this.child).hide();
			} else {
				this.el.addClassName('expanded');
			}

			Event.observe(this.el, 'click', this.toggle.bind(this));
			this.el.observe('CollapseHeadings:collapse',this.collapse.bind(this));
			this.el.observe('CollapseHeadings:expand',this.expand.bind(this));
			document.observe('CollapseHeadings:collapse-all', this.collapse.bind(this));
			document.observe('CollapseHeadings:expand-all', this.expand.bind(this));
		}
	},

	toggle: function() {
		if ($(this.child).visible()) {
			this.collapse();
		} else {
			this.expand();
		}
	},

	collapse: function () {
		if ($(this.child).visible()) {
			this.el.removeClassName('expanded');
			this.el.addClassName('collapsed');

			Effect.BlindUp(this.child, { duration: 0.3 })
		} // else already collapsed
	},
	expand: function () {
		if (!$(this.child).visible()) {
			this.el.removeClassName('collapsed');
			this.el.addClassName('expanded');

			Effect.BlindDown(this.child, { duration: 0.3 })
		} //else already expanded
	}
});

document.observe("dom:loaded", function() {
	$$('textarea.expandable').each(function(el) {
		new ExpandableTextarea(el);
	});

	$$('h2','.collapsable').each(function (el) {
		new CollapseHeadings(el);
	});

	$$('ul.page-action > li:last-child').each(function (el) {
		el.addClassName('last');
	});
});

// Used on the Adding / Editing Calendar Events page.
function checkForNewRegion() {
	if(document.getElementById('region_id').options[document.getElementById('region_id').selectedIndex].value == 'new') {
		document.getElementById('new_region_layer').style.display = '';
		document.getElementById('new_region').focus();
	} else {
		document.getElementById('new_region_layer').style.display = 'none';
		document.getElementById('region_id').focus();
	}
}

var grow;

function growPic(official_photo, uploaded_photo, official_link, uploaded_link, zoomout) {
	if (!grow) {

		$$('.zoomin').each(function (e) { e.innerHTML = ''; });

		if (official_photo) {
			new Effect.Scale(official_photo, 300,
			{
				scaleMode:
				{
					originalHeight:	100,
					originalWidth:	72
				},
				beforeStart: function() {
					official_photo.style.zIndex = 8;
				},
				afterFinish: function() {
					zoomout.innerHTML = '-';

					grow = true;
				}
			});

			if (official_link) {
				official_link.style.zIndex = 10;
				new Effect.Morph(official_link,
				{
					style: 'left: 15px; bottom: -185px; font-size: 24px; line-height: 26px; padding: 0px 5px 0px 5px;',
					duration: 1.0
				});
			}
		}

		if (uploaded_photo) {
			new Effect.Scale(uploaded_photo, 300,
			{
				scaleMode:
				{
					originalHeight:	100,
					originalWidth:	72
				},
				beforeStart: function() {
					uploaded_photo.style.zIndex	= 7;
				},
				afterFinish: function() {
					zoomout.innerHTML = '-';

					grow = true;
				}
			});

			if (uploaded_link) {
				uploaded_link.style.zIndex = 10;
				new Effect.Morph(uploaded_link,
				{
					style: 'left: 47px; bottom: -185px; font-size: 24px; line-height: 26px; padding: 0px 5px 0px 5px;',
					duration: 1.0
				});
			}
		}
	}

	return false;
}

function shrinkPic(official_photo, uploaded_photo, official_link, uploaded_link, zoomout) {
	if ((official_photo && official_photo.width > 72) || (uploaded_photo && uploaded_photo.width > 72)) {

		zoomout.innerHTML = '';

		if (official_photo) {
			new Effect.Scale(official_photo, 100,
			{
				scaleFrom: (official_photo.width / 72 * 100),
				scaleMode:
				{
					originalHeight:	100,
					originalWidth:	72
				},
				afterFinish: function() {
					$$('.zoomin').each(function (e) { e.innerHTML = '+'; });

					official_photo.style.zIndex = 6;

					grow = false;
				}
			});

			if (official_link) {
				new Effect.Morph(official_link,
				{
					style: 'left: 5px; bottom: 5px; font-size: 9px; line-height: 10px;  padding: 0px 2px 0px 2px;',
					duration: 1.0
				});
				official_link.style.zIndex = 6;
			}
		}

		if (uploaded_photo) {
			new Effect.Scale(uploaded_photo, 100,
			{
				scaleFrom: (uploaded_photo.width / 72 * 100),
				scaleMode:
				{
					originalHeight: 100,
					originalWidth: 72
				},
				afterFinish: function() {
					$$('.zoomin').each(function (e) { e.innerHTML = '+'; });

					uploaded_photo.style.zIndex = 5;

					grow = false;
				}
			});

			if (uploaded_link) {
				new Effect.Morph(uploaded_link,
				{
					style: 'left: 19px; bottom: 5px; font-size: 9px; line-height: 10px; padding: 0px 2px 0px 2px;',
					duration: 1.0
				});
				uploaded_link.style.zIndex = 6;
			}
		}
	}

	return false;
}

var transitionRunning = false;

function hideOfficial(official_photo, active, inactive) {
	if (!transitionRunning) {
		transitionRunning = true;
		new Effect.Fade(official_photo,
		{
			duration: 0.3,
			to: 0.0,
			afterFinish: function() {
				transitionRunning	= false;
			}
		});
	}
}

function showOfficial(official_photo, active, inactive) {
	if (!transitionRunning) {
		transitionRunning = true;
		new Effect.Appear(official_photo, {
			duration: 0.3,
			to: 1.0,
			afterFinish: function() {
				transitionRunning = false;
			}
		});
	}
}

function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}

/**
 * Allows specification of a select all check box and a defined group of slaves to it. clicking the master (select all) will select/de-select all slaves. clicking one of the slaves may check/uncheck the master depending on the state of th other checkboxes (all checked -> master checked, one or more unchecked -> master unchecked)
 * @param master element which acts as the "selecct all" checkbox
 * @param slaves css selector pattern, or nodelist/array of elements
 */
function CheckboxCheckAll(master,slaves) {
	//if slaves is a string, then use it as a pattern and, if not, use the nodes

	function getSlaves() {
		if (typeof slaves == "string") {
			return $$(slaves);
		} else return slaves;
	}

	function getMaster() {
		return $(master);
	}

	function checkAll(event) {
		var state = getMaster().checked;
		getSlaves().reject(isDisabled).each(function (el) { el.checked=state; });
	}

	function areAllChecked() {
		return getSlaves().reject(isDisabled).pluck("checked").all();
	}

	function setCheckAll() {
		var state = areAllChecked();
		getMaster().checked=state;
	}

	this.disable = function () {
		getMaster().stopObserving('click',checkAll);
		getSlaves().invoke("stopObserving","click",setCheckAll);
	}

	this.enable = function() {
		var slaves = getSlaves();
		getMaster().observe('click',checkAll);
		slaves.invoke("observe","click",setCheckAll);
	}
	this.enable();
}

/**
 * Returns true if the passed element has a disabled property with a truthy value. false otherwise.
 * @param element
 * @return boolean
 */
function isDisabled(element) {
	return (!!(element.disabled));
}

/**
 *  Returns true if console.log is available. false otherwise.
 *  @return boolean
 */
function hasConsole() {
	return (typeof console != "undefined") && (console.log) && (typeof console.log == "function");
}

/**
 * passes arguments to console.log if it is available. otherwise does nothing.
 */
function clog() {
	return false;
}

/**
 * Capitalize the first letter of a string (i.e. word).
 */
function capitalizeFirstLetter(word)
{
   if (typeof word == "string") {
     return word.charAt(0).toUpperCase() + word.slice(1);
   } else {
     return word;
   }
}
