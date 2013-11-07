var s;
var entrada = {
	settings: {
		api_url: 'http://130.15.42.75/entrada-1x/www-root',
		firstname: '',
		lastname: '',
		selected_group: window.localStorage.getItem('selected_group'),
		selected_role: ''
	},
	
    init: function() {
		s = entrada.settings;
		entrada.initScheduler();
		this.bindEvents();
		entrada.setAuthPreference();

		entrada.authenticate(function (response) {
			if (response.authenticated == 'true') {
				entrada.registerDevice();
				$("#username").prop('disabled', true);
				$("#password").prop('disabled', true);
				$('.open-panel').show();
				$('.ui-btn-right').show();
				dhx.Touch.enable();
				entrada.setUserName(response.firstname, response.lastname);
				s.firstname = response.firstname;
				s.lastname = response.lastname;
				entrada.buildGroupsMenu('user-groups-roles-agenda');
                if ($.mobile.activePage.is("#evaluation-attempt")) {
                    $.mobile.changePage('#evaluations', {transition: 'slideup'});
                }
			} else {
				$.mobile.changePage('#settings', {transition: 'slideup'});
				$('.open-panel').hide();
				$('.ui-btn-right').hide();
				$("#username").prop('disabled', false);
				$("#password").prop('disabled', false);
			}
		});
    },
    
	bindEvents: function() {
		StatusBar.overlaysWebView(false);
		document.addEventListener('offline', entrada.onOffline, false);
		document.addEventListener('online', entrada.onOnline, false);
		
		$.mobile.defaultPageTransition = 'none';
		
		dhx.env.mobile = true;
		dhx.env.touch = true;
		
		$('.group-menu').on('change', function () {
			
			var menu_id = entrada.getMenuId();
			
			window.localStorage.setItem('selected_group', $(this).find(':selected').val());
			window.localStorage.setItem('selected_role', $(this).find(':selected').data('userrole'));
			
			entrada.loadMenu($(this).find(":selected").val(), menu_id);
			entrada.loadCurrentPageData();
			
		});
		
		$(window).bind("resize", function () {
			entrada.schedulerSize();
		});
		
		$(document).ajaxStart(function() {
			$.mobile.showPageLoadingMsg("a", "Loading...");
		});

		$(document).ajaxStop(function() {
			$.mobile.hidePageLoadingMsg();
		});
		
		
		$('#save-settings').on('tap', function () {
			entrada.authenticate(function (response) {
				if (response.authenticated == 'true') {
					$("#username").prop('disabled', true);
					$("#password").prop('disabled', true);
					entrada.registerDevice();
					$('.open-panel').show();
					$('.ui-btn-right').show();
					$.mobile.changePage('#agenda', {transition: 'slidedown'});
					
					dhx.Touch.enable();
					entrada.setUserName(response.firstname, response.lastname);

					s.firstname = response.firstname;
					s.lastname = response.lastname;
					
					entrada.buildGroupsMenu('user-groups-roles-agenda');
					
					var selected = $('input[name="login-option"]:checked');
					
					if (selected.val() == '1') {
						window.localStorage.setItem('hash', response.hash);
					} else {
						entrada.enableAuthControls();
					}
					
				} else {
					navigator.notification.alert('Invalid credentials');
					$("#username").prop('disabled', false);
					$("#password").prop('disabled', false);
				}
			});
		});
		
		$('#logout').bind('tap', function () {
			entrada.logout();
		});
		
		$('.agenda').bind('click', function () {
			dhx.Touch.enable();
		});
		
		$('#agenda').on('beforepageshow', function () {
			
			entrada.buildGroupsMenu('user-groups-roles-agenda');
			entrada.loadMenu($('#user-groups-roles-agenda').find(':selected').val(), 'menu-list-agenda');
			$('#agenda-menu-item').addClass('ui-btn-active');
			
		});
		
		$('#events').on('pagebeforeshow', function () {
			
			entrada.buildGroupsMenu('user-groups-roles-events');
			entrada.loadMenu(window.localStorage.getItem('selected_group'), 'menu-list-events');
			
			dhx.Touch.disable();
			entrada.fetchNotices();
	
		});
		
		$('#objectives').on('pagebeforeshow', function () {
			
			dhx.Touch.disable();
			entrada.buildGroupsMenu('user-groups-roles-objectives');
			entrada.loadMenu(window.localStorage.getItem('selected_group'), 'menu-list-objectives');
			entrada.fetchObjectives();
			
		});
		
		$('#evaluations').on('pagebeforeshow', function () {
		
			entrada.buildGroupsMenu('user-groups-roles-evaluations');
			entrada.loadMenu(window.localStorage.getItem('selected_group'), 'menu-list-evaluations');
			entrada.loadEvaluations(null);
			
		});
		
		$('#evaluation-attempt').on('pagebeforeshow', function () {
			entrada.buildGroupsMenu('user-groups-roles-evaluation-attempt');
			entrada.loadMenu(window.localStorage.getItem('selected_group'), 'menu-list-evaluation-attempt');			
		});
		
		$('#settings').on('pagebeforeshow', function () {
			
			dhx.Touch.disable();
			
			entrada.buildGroupsMenu('user-groups-roles-settings');
			entrada.loadMenu(window.localStorage.getItem('selected_group'), 'menu-list-settings');
			
		});
    },
	
	loadMenu: function (group, menu_element_id) {
		
		var menu_element = '#' + menu_element_id;
		
		switch (group) {
			case 'Student' :
				entrada.buildStudentMenu(menu_element);
			break;
			case 'Faculty' :
				entrada.buildFacultyMenu(menu_element);
			break;
			default:
				entrada.buildDefaultMenu(menu_element);
			break;
		}
	},
	
	buildStudentMenu: function (menu_element) {
		var current_page = $.mobile.activePage.attr('id');
		$(menu_element).empty();
		var element_agenda = $('<li/>').append($('<a/>', {'href': '#agenda', 'text': 'My Agenda', 'class': (current_page == 'agenda') ? 'ui-btn-active' : ''}));
		$(menu_element).append(element_agenda);
		var element_notices = $('<li/>').append($('<a/>', {'href': '#events', 'text': 'My Notices', 'class': (current_page == 'events') ? 'ui-btn-active' : ''}));
		$(menu_element).append(element_notices);
		var element_evaluations = $('<li/>').append($('<a/>', {'href': '#evaluations', 'text': 'My Evaluations', 'class': (current_page == 'evaluations') ? 'ui-btn-active' : ''}));
		$(menu_element).append(element_evaluations);
		var element_settings = $('<li/>').append($('<a/>', {'href': '#settings', 'text': 'Settings', 'class': (current_page == 'settings') ? 'ui-btn-active' : ''}));
		$(menu_element).append(element_settings);
		$(menu_element).listview('refresh');
	},
	
	buildFacultyMenu: function (menu_element) {
		var current_page = $.mobile.activePage.attr('id');
		$(menu_element).empty();
		var element_agenda = $('<li/>').append($('<a/>', {'href': '#agenda', 'text': 'My Agenda', 'class': (current_page == 'agenda') ? 'ui-btn-active' : ''}));
		$(menu_element).append(element_agenda);
		var element_notices = $('<li/>').append($('<a/>', {'href': '#events', 'text': 'My Notices', 'class': (current_page == 'events') ? 'ui-btn-active' : ''}));
		$(menu_element).append(element_notices);
		var element_evaluations = $('<li/>').append($('<a/>', {'href': '#evaluations', 'text': 'My Evaluations', 'class': (current_page == 'evaluations') ? 'ui-btn-active' : ''}));
		$(menu_element).append(element_evaluations);
		//var element_redbook = $('<li/>').append($('<a/>', {'href': '#objectives', 'text': 'Redbook Objectives', 'class': (current_page == 'objectives') ? 'ui-btn-active' : ''}));
		//$(menu_element).append(element_redbook);
		var element_settings = $('<li/>').append($('<a/>', {'href': '#settings', 'text': 'My Settings', 'class': (current_page == 'settings') ? 'ui-btn-active' : ''}));
		$(menu_element).append(element_settings);
		$(menu_element).listview('refresh');
	},
	
	buildDefaultMenu: function (menu_element) {
		var current_page = $.mobile.activePage.attr('id');
		$(menu_element).empty();
		var element_agenda = $('<li/>').append($('<a/>', {'href': '#agenda', 'text': 'My Agenda', 'class': (current_page == 'agenda') ? 'ui-btn-active' : ''}));
		$(menu_element).append(element_agenda);
		var element_notices = $('<li/>').append($('<a/>', {'href': '#events', 'text': 'My Notices', 'class': (current_page == 'events') ? 'ui-btn-active' : ''}));
		$(menu_element).append(element_notices);
		var element_settings = $('<li/>').append($('<a/>', {'href': '#settings', 'text': 'My Settings', 'class': (current_page == 'settings') ? 'ui-btn-active' : ''}));
		$(menu_element).append(element_settings);		
		$(menu_element).listview('refresh');
	},
	
	fetchNotices: function () {		
		var username = $('#username').val();
		var password = $('#password').val();
		var total_notices = $('.notice_list').length;
		$.ajax({
			url: s.api_url + '/api/mobile.api.php',
			type: 'post',
			data: ({method:'notices', total_notices:total_notices, username:username, password:password, hash:window.localStorage.getItem('hash'), group: window.localStorage.getItem('selected_group')}),
			success:function (data) {
				if (data != 'false') {
					
					if ($('.no-notices').length > 0) {
						$('.no-notices').remove();
					}

					if ($('.no-old-notices').length > 0) {
						$('.no-old-notices').remove();
					}

					$('.notice_list').remove();
					var response = JSON.parse(data);
					var notice_ids = new Array();
					if (response.status == "success") {
						var data_theme = null;
						$.each(response.data, function (key, notice) {
							notice_ids.push(notice.notice_id);
							var data_theme = ((parseInt($.trim(notice.notice_id)) > parseInt($.trim(response.max_notice_id))) ? "e" : "b");
							if (notice.notice_status == 'new') {
								var new_html = '<div data-role="collapsible" data-content-theme="'+ data_theme +'" data-theme="'+ data_theme +'" class="notice_list new-notice" id="notice'+ notice.notice_id +'">';
								new_html += '<h2 id="notice_'+ notice.notice_id +'_date">'+ notice.updated_date +'</h2>';
								new_html += '<ul data-role="listview" data-theme="d">';
								new_html += '<li>';
								new_html += '<div data-role="controlgroup" data-type="horizontal" style="position:relative">';
								new_html += '<a href="#" data-role="button" data-inline="true" data-theme="c" data-icon="check" class="mark" style="position:absolute; right:10px; top:-10px" data="'+ notice.notice_id +'">Mark as Read</a>';
								new_html += '</div>';
								new_html += '</li>';
								new_html += '<li id="notice_'+ notice.notice_id +'_summary">';
								new_html += notice.notice_summary;
								new_html += '</li>';
								new_html += '</ul>';
								new_html += '</div>';
								$('#notice_content').append(new_html);
							}

							if (notice.notice_status == 'read') {
								var html = '<div data-role="collapsible" data-content-theme="c" data-theme="c" class="notice_list old-notice">';
								html += '<h2>'+ notice.updated_date +'</h2>';
								html += '<ul data-role="listview" data-theme="d">';
								html += '<li>';
								html += notice.notice_summary;
								html += '</li>';
								html += '</ul>';
								html += '</div>';
								$('#old_notice_content').append(html);
							}
						});
					}
					
					if (!$('.new-notice').length > 0) {
						$('#notice_content').append('<p class="no-notices">No New Notices to Display</p>');
					}

					if (!$('.old-notice').length > 0) {
						$('#old_notice_content').append('<p class="no-old-notices">No Previously Read Notices to Display</p>');
					}
					
					//$('#unread_notice_content').trigger("create");
					$('#notice_content').trigger("create");
					$('#old_notice_content').trigger("create");

					$('.mark').bind('click', function (e) {
						e.preventDefault();
						var notice_id = $(this).attr('data');
						$.ajax({
							url: s.api_url + '/api/mobile.api.php',
							type: 'post',
							data: ({method:'mark', notice_id:notice_id, username:username, password:password, hash:window.localStorage.getItem('hash')}),
							dataType: 'text',
							success:function () {
								$('.ui-collapsible-heading-status').remove();
								var html = '<div data-role="collapsible" data-content-theme="c" data-theme="c" class="notice_list old_notice">';
								html += '<h2>'+ $('#notice_' + notice_id + '_date').text() +'</h2>';
								html += '<ul data-role="listview" data-theme="d">';
								html += '<li>';
								html += $('#notice_' + notice_id + '_summary').text();
								html += '</li>';
								html += '</ul>';
								html += '</div>';
								$('#old_notice_content').append(html);
								$('#notice' + notice_id).remove();

								if (!$('.new-notice').length > 0) {
									$('#notice_content').append('<p class="no-notices">No New Notices to Display</p>');
								}

								if ($('.no-old-notices').length > 0) {
									$('.no-old-notices').remove();
								}

								$('#old_notice_content').trigger("create");

							}, error: function (event, request, settings, exception, data) {

							} 
						});
					});
					entrada.updateMaxNoticeId(Math.max.apply(Math, notice_ids));
				}
				
			}, error: function (error1, error2, data) {
			} 
		});
	},
	
	loadCurrentPageData: function () {
		
		var current_page = $.mobile.activePage.attr('id');
		
		switch (current_page) {
			case 'agenda' :
				entrada.loadScheduler($('#user-groups-roles-agenda').find(':selected').val(), $('#user-groups-roles-agenda').find(':selected').data('userrole'));
			break;
			case 'events' :
				entrada.fetchNotices();
			break;
		}
		
	},
	
	getMenuId: function () {
		var active_page = $.mobile.activePage;
		return 'menu-list-' + active_page.attr('id');
	},
	
	getGroupMenuId: function () {
		var active_page = $.mobile.activePage;
		return 'user-groups-roles-' + active_page.attr('id');
	},
	
	buildGroupsMenu: function (group_menu_element) {
		
		var username = $('#username').val();
		var password = $('#password').val();
		
		var menu_element_id = '#' + group_menu_element;
		
		$(menu_element_id).empty();
		
		$.ajax({
			url: s.api_url + '/api/mobile.api.php',
			type: 'post',
			data: ({method:'groups', username: username, password: password, hash: window.localStorage.getItem('hash')}),
			dataType: 'text',
			success:function (data) {
				obj = JSON.parse(data);
				$(menu_element_id).append($('<option/>', {'class': 'user-name', 'text': s.firstname + ' ' + s.lastname}));
				entrada.setUserName(s.firstname, s.lastname);
				$.each(obj, function (i, group) {
					
					$(menu_element_id).append($('<option/>', { 
						'value': group.group,
						'text' : group.group,
						'data-userrole': group.role
					}));
				});
				
				if (window.localStorage.getItem('selected_group') != null || window.localStorage.getItem('selected_group') != undefined) {
		
					$(menu_element_id).val(window.localStorage.getItem('selected_group'));
					
					if ($.mobile.activePage.attr('id') == 'agenda') {
						entrada.loadScheduler($(menu_element_id).find(':selected').val(), $(menu_element_id).find(':selected').data('userrole'));
					}
					
				} else {
					
					$(menu_element_id + ' option:eq(1)').attr('selected', 'selected');
					
					if ($.mobile.activePage.attr('id') == 'agenda') {
						entrada.loadScheduler($(menu_element_id + ' option:eq(1)').val(), $(menu_element_id + ' option:eq(1)').data('userrole'));
					}
					
					
				}
				
				$(menu_element_id).selectmenu('refresh');
				var menu_id = entrada.getMenuId();
				
				entrada.loadMenu($(menu_element_id).find(':selected').val(), menu_id);
				
			}, error: function (event, request, settings, exception, data) {
				navigator.notification.alert('An error occured while building the group navigation');
			} 
		});
	},
	
	initScheduler: function () {
		scheduler.config.readonly = true;
		scheduler.config.header_date = "%M %j, %Y";
		scheduler.config.hour_date = "%h:%i%a";
		scheduler.config.scale_hour = "%h";
		dhx.ready(function(){
			dhx.Touch.enable();
			dhx.ui({
				view: "scheduler",
				container: "content",
				id: "scheduler"
			});

			entrada.schedulerSize();
			$$("scheduler").$$("buttons").setValue("day");
			$$("scheduler").$$("day").show();
			//$$("scheduler").$$("dayList").scrollTo(0,248);
		});
	},
	
	loadScheduler: function (group, role) {
		
		var username = $('#username').val();
		var password = $('#password').val();
		
		$.ajax({
			url: s.api_url + '/api/mobile.api.php',
			type: 'post',
			data: ({method:'agenda', username: username, password: password, hash: window.localStorage.getItem('hash'),  group: group, role: role}),
			dataType: 'json',
			success:function (data) {
				$$("scheduler").clearAll(); 
				$$("scheduler").parse(data);
				$$("scheduler").$$("dayList").render();
				//$$("scheduler").$$("dayList").scrollTo(0,248);
			}, error: function (event, request, settings, exception, data) {
				navigator.notification.alert('An error occured while loading the calendar');
			} 
		});
	},
	
	schedulerSize: function () {
		var width = $("html").width();
		var height = $("html").height() - 43;
		$("#content").css("height", height);
		$$("scheduler").define("width", width);
		$$("scheduler").define("height", height);
		$$("scheduler").resize();
		$(".dhx_dayevents_scale_event").css("width", width - 45);
		$(".dhx_dayevents_scale_event").children("div").each(function () {
			$(this).css("width", width - 44);
		});
		//$$("scheduler").$$("dayList").scrollTo(0,248);
	},
	
	fetchObjectives: function () {
		
		var username = $('#username').val();
		var password = $('#password').val();
		
		$('.objective').remove();
		
		$.ajax({
			url: s.api_url + '/api/mobile.api.php',
			type: 'post',
			data: ({method:'objectives', username: username, password: password, hash: window.localStorage.getItem('hash'),  group: window.localStorage.getItem('selected_group'), role: window.localStorage.getItem('selected_role')}),
			dataType: 'json',
			success:function (data) {
				
				$.each(data, function (i, objective) {
					$('<div/>', {'id': objective.objective_name,'data-role': 'collapsible', 'data-theme': 'b', 'class': 'objective'}).appendTo('#objectives-content');
					$('<h3/>', {'text': objective.objective_name}).appendTo('#'+ objective.objective_name);
				});
				
				$('#objectives-content').trigger('create');
				
			}, error: function (event, request, settings, exception, data) {
				navigator.notification.alert('An error occured while loading the calendar');
			}
		});
	},
	
	loadEvaluations: function (prepend_html) {
		
		var username = $('#username').val();
		var password = $('#password').val();
		
		dhx.Touch.disable();
		
		var total_evaluations = $('.evaluation_list').length;
		$('.evaluation_list').remove();
		//$('#evaluation_loading').html('<div style=\"width: 100%; margin: 0 auto; text-align: center;\"><img src=\"img/loading.gif\" /></div>');
		
		$.ajax({
			url: s.api_url + '/api/mobile.api.php',
			type: 'post',
			data: ({method:'evaluations', total_evaluations:total_evaluations, username: username, password: password, hash:window.localStorage.getItem('hash')}),
			dataType: 'text',
			success:function (data) {
				
				if ($('.no-evaluations').length > 0) {
					$('.no-evaluations').remove();
				}
				if (data != 'false') {
					
					
					
					$('#evaluation_loading').html('&nbsp;');
					$('.evaluation_list').remove();
					
					if (prepend_html != undefined && prepend_html != null) {
						$('#evaluation_content').append(prepend_html);
					}
					
					var obj = JSON.parse(data);
					$.each(obj, function (key, val) {
						var new_html = '<div data-role="collapsible" data-content-theme="c" class="evaluation_list new-evaluation" id="evaluation'+ val.evaluation_id +'">';
						new_html += '<h2 id="evaluation_'+ val.evaluation_id +'_title">'+ val.evaluation_title +'</h2>';
						new_html += '<ul data-role="listview" data-theme="d">';
						new_html += '<li>';
						new_html += '<div data-role="controlgroup" data-type="horizontal" style="position:relative">';
						new_html += '<a href="#" data-role="button" data-inline="true" data-theme="c" data-icon="check" class="attempt" style="position:absolute; right:10px; top:-10px" data="'+ val.evaluation_id +'">Complete Evaluation</a>';
						new_html += '</div>';
						new_html += '</li>';
						new_html += '<li id="evaluation_'+ val.evaluation_id +'_summary">';
						new_html += val.evaluation_description;
						new_html += '</li>';
						new_html += '</ul>';
						new_html += '</div>';
						
						$('#evaluation_content').append(new_html);
						
					});
					
					
					$('#evaluation_content').trigger("create");

					$('.attempt').bind('click', function (e) {
						
						e.preventDefault();
						var evaluation_id = $(this).attr('data');
						entrada.submitEvaluationAttempt(s.api_url, evaluation_id, null, username, password, window.localStorage.getItem('hash'));
					
					});
				}
					
				if (!$('.evaluation_list').length > 0) {
					
					$('#evaluation_content').append('<p class="no-evaluations">No Evaluations Available to Complete</p>');
					
				}

			}, error: function (error1, error2, data) {
				$('#evaluation_loading').html('<div class=\"display-notice\"><ul><li>An error occured while loading your evaluations.</li><ul></div>');
				navigator.notification.alert('An error occured while loading your evaluations');
			} 
		});
	},
	
	submitEvaluationAttempt: function (url, evaluation_id, attempt_data, username, password, hash) {
		
		if (!attempt_data) {
			attempt_data = {method:'evaluationattempt', evaluation_id:evaluation_id, username: username, password: password, hash:window.localStorage.getItem('hash')};
		}
		
		$.ajax({
			url: s.api_url + '/api/mobile.api.php',
			type: 'post',
			data: attempt_data,
			dataType: 'text',
			success:function (data) {
				
				if (data != 'false') {
					$('#evaluation_attempt').remove();
					var obj = JSON.parse(data);
					if (obj != undefined && obj['evaluation_attempt'] && obj['success_status']) {
						if (obj['success_status'] == 'false') {
							$('#evaluation_attempt_content').append(obj['evaluation_attempt']);
							$('#evaluation_attempt_content').trigger("create");
							$('#evaluation-submit').bind('click', function (e) {
								e.preventDefault();
								if (!$('#evalhash').val() || $('#evalhash').val() == null) {
									$('#evalusername').val(username);
									$('#evalpassword').val(password);
								}
								entrada.submitEvaluationAttempt(url, evaluation_id, $('#evaluation-form').serialize(), username, password, hash);
							});
							$.mobile.changePage('#evaluation-attempt', {transition: 'slideup'});
						} else {
							$.mobile.changePage('#evaluations', {transition: 'slideup'});
							entrada.loadEvaluations(obj['evaluation_attempt']);
						}
					} else {
						navigator.notification.alert('An error occured while loading the evaluation to attempt');
					}
				} else {
					navigator.notification.alert('An error occured while loading the evaluation to attempt');
				}
			}, error: function (error1, error2, data) {
				navigator.notification.alert('An error occured while loading the evaluation to attempt');
			} 
		});
	},
	
	setUserName: function (firstname, lastname) {
		$('.user-name').text(firstname + ' ' + lastname);
	},
	
	setAuthPreference : function () {
		if (window.localStorage.getItem('hash') != null) {
			$('#save-password').prop('checked', true);
			//entrada.disableAuthControls();
		}
	},
	
	disableAuthControls: function () {
		
		$('#username').prop('disabled', true);
		$('#password').prop('disabled', true);
		
	},
	
	enableAuthControls: function () {
		
		$('#username').textinput('enable');
		$('#password').textinput('enable');
		
	},
	
	authenticate: function (callback) {
		
		var username = $('#username').val();
		var password = $('#password').val();
		hash = window.localStorage.getItem('hash');
		
		$.ajax({
			url: s.api_url + '/api/mobile.api.php',
			type: 'post',
			dataType: 'json',
			data: ({method:'hash', username:username, password:password, hash:hash}),
			success: callback,	
			error: function (error1, error2, data) {
				
				navigator.notification.alert('An error occured during authentication');
				$.mobile.changePage('#settings', {transition: 'slideup'});
				
				$('.open-panel').hide();
				$('.ui-btn-right').hide();
				
			} 
		});
	},
	
	logout: function () {
		
		$('#settings').trigger('updatelayout');
		$$("scheduler").clearAll();
		$$("scheduler").$$("dayList").render();
		$('.new-notice, .old-notice').remove();

		if (!$('.new-notice').length > 0) {
			$('#notice_content').append('<p class="no-notices">No New Notices to Display</p>');
		}

		if (!$('.old-notice').length > 0) {
			$('#old_notice_content').append('<p class="no-old-notices">No Previously Read Notices to Display</p>');
		}

		window.localStorage.removeItem('hash');
		window.localStorage.removeItem('auth_preference');
		window.localStorage.removeItem('selected_group');
		
		$('.open-panel').hide();
		$('.ui-btn-right').hide();
		
		$('#username').val('');
		$('#password').val('');
		$("#username").removeAttr("disabled");
		$("#password").removeAttr("disabled");
		$('#settings').trigger('create');
	},
	
	tokenHandler: function (token) {
        alert("tokenHandler fired");
		var username = $('#username').val();
		var password = $('#password').val();
		hash = window.localStorage.getItem('hash');
		
		$.ajax({
			url: s.api_url + '/api/mobile.api.php',
			type: 'post',
			dataType: 'json',
			data: ({method:'registertoken', username:username, password:password, hash:hash, device_token:token}),
			success: function (data) {
				//console.log(data);
			},	
			error: function (error1, error2, data) {
				
			} 
		});
	},
	
	errorHandler: function (error) {
		alert(error);
	},
	
	onNotificationAPN: function (event) {
        if (device.platform === "iOS") {
            if (event.alert) {
                alert(event.message);
                $.mobile.changePage("#events").trigger("create");
            }
    
            if (event.sound) {
                var snd = new Media(event.sound);
                snd.play();
            }
    
            if (event.badge) {
                pushNotification.setApplicationIconBadgeNumber(entrada.successHandler, errorHandler, event.badge);
            }
        } else if (device.platform === "Android") {
            switch(event.event) {
                case 'registered':
                    if ( e.regid.length > 0 )
                    {
                        console.log("Regid " + e.regid);
                        alert('registration id = '+e.regid);
                    }
                break;
     
                case 'message':
                  // this is the actual push notification. its format depends on the data model from the push server
                  alert('message = '+e.message+' msgcnt = '+e.msgcnt);
                break;
     
                case 'error':
                  alert('GCM error = '+e.msg);
                break;
     
                default:
                  alert('An unknown GCM event has occurred');
                break;
            }
        }
		
	},
	
	registerDevice: function () {
		try {
			var pushNotification = window.plugins.pushNotification;
            
            if (device.platform === "iOS" || device.platform === "iOS") {
                pushNotification.register(entrada.tokenHandler, entrada.errorHandler, {"badge":"true","sound":"true","alert":"true","ecb":"entrada.onNotificationAPN"});
            } else if (device.platform === "Android" || device.platform === "android") {
                alert("android");
                pushNotification.register(entrada.tokenHandler, entrada.errorHandler, {"senderID": "710282505910", "ecb": "entrada.onNotificationAPN"});
            }
            
		} catch(error) {
			txt="There was an error on this page.\n\n"; 
			txt+="Error description: " + error.message + "\n\n"; 
			alert(txt);
		}
	},
	
	getMaxNoticeId: function () {
		var username = $('#username').val();
		var password = $('#password').val();
		var max_notice_id = null;
		hash = window.localStorage.getItem('hash');
		
		$.ajax({
			url: s.api_url + '/api/mobile.api.php',
			type: 'post',
			dataType: 'json',
			data: ({method:'maxnoticeid', username:username, password:password, hash:hash}),
			success: function (data) {
				max_notice_id = data.data;
			},	
			error: function (error1, error2, data) {
			} 
		});
		return max_notice_id;
	},
	
	updateMaxNoticeId: function (max_notice_id) {
		var username = $('#username').val();
		var password = $('#password').val();
		hash = window.localStorage.getItem('hash');
		
		$.ajax({
			url: s.api_url + '/api/mobile.api.php',
			type: 'post',
			dataType: 'json',
			data: ({method:'updatenoticeid', username:username, password:password, hash:hash, max_notice_id:max_notice_id}),
			success: function (data) {
				max_notice_id = data.data;
			},	
			error: function (error1, error2, data) {
			} 
		});
	},
	
	successHandler: function () {
		alert("SUCCESS");
	},
	
	onOffline: function () {
		$.mobile.changePage("#network", {transition: "fade"});
	},
	
	onOnline: function () {
		entrada.authenticate(function (response) {
			if (response.authenticated == 'true') {
				$.mobile.changePage("#agenda", {transition: "fade"});
				$('.open-panel').show();
				$('.ui-btn-right').show();
				
				dhx.Touch.enable();
				entrada.setUserName(response.firstname, response.lastname);
				s.firstname = response.firstname;
				s.lastname = response.lastname;
				entrada.buildGroupsMenu('user-groups-roles-agenda');
			} else {
				$.mobile.changePage('#settings', {transition: 'slideup'});
				$('.open-panel').hide();
				$('.ui-btn-right').hide();
			}
		});
	}
};