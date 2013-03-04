var app = {
    initialize: function() {	
		this.bindEvents();
    },
    bindEvents: function() {
		var jqmReady = $.Deferred(),
			pgReady = $.Deferred();
		$(document).bind("pageinit", jqmReady.resolve);
		document.addEventListener("deviceready", pgReady.resolve, false);
		
		//When jqm and phonegap are both ready call init
		$.when(jqmReady, pgReady).then(function () {
		  $(document).trigger('init');
		});
		
		$(document).bind('init', function () {
			dhx.env.mobile = true;
			dhx.env.touch = true;
			initScheduler();
			window.url = $('#url').val();
			var username = '';
			var password = '';
					
			if (window.localStorage.getItem('hash') != null) {
				$.ajax({
					url: 'http://' + window.url + '/api/mobile.api.php',
					type: 'post',
					data: ({method:'credentials', hash:window.localStorage.getItem('hash')}),
					dataType: 'text',
					success:function (data) {
						if (data == true) {
							$('#save-password').prop('checked', true);
							loadApp();			
						} else {
							navigator.notification.alert('Invalid credentials');
							$.mobile.changePage('#settings', { transition: "flip"});
							$('.close').hide();
						}
					}, error: function (event, request, settings, exception, data) {
						navigator.notification.alert('An error occured during authentication');
					} 
				});
			} else if (window.localStorage.getItem('hash') == null) {
				$.mobile.changePage('#settings', { transition: "flip"});
				$('.close').hide();
			}
			
			function loadApp () {
				loadScheduler ();
				setInterval(function () {
					loadScheduler();
				}, 900000);
				
				$('.agenda').bind('touchstart', function () {
					if (!$(this).hasClass('ui-btn-active')) {
						$('.agenda').addClass("ui-btn-active");
					}

					if ($('.notices').hasClass('ui-btn-active')) {
						$('.notices').removeClass('ui-btn-active');
					}

					dhx.Touch.enable();
				});

				$('.notices').bind('touchstart', function () {
					if (!$(this).hasClass('ui-btn-active')) {
						$('.notices').addClass("ui-btn-active");
					}

					if ($('.agenda').hasClass('ui-btn-active')) {
						$('.agenda').removeClass('ui-btn-active');
					}

					dhx.Touch.disable();
					var total_notices = $('.notice_list').length;
					$.ajax({
						url: 'http://' + window.url + '/api/mobile.api.php',
						type: 'post',
						data: ({method:'notices', total_notices:total_notices, username:username, password:password, hash:window.localStorage.getItem('hash')}),
						dataType: 'text',
						success:function (data) {
							if (data != 'false') {
								if ($('.no-notices').length > 0) {
									$('.no-notices').remove();
								}

								if ($('.no-old-notices').length > 0) {
									$('.no-old-notices').remove();
								}

								$('.notice_list').remove();
								var obj = JSON.parse(data);
								$.each(obj, function (key, val) {
									if (val.notice_status == 'new') {
										var new_html = '<div data-role="collapsible" data-content-theme="c" class="notice_list new-notice" id="notice'+ val.notice_id +'">';
										new_html += '<h2 id="notice_'+ val.notice_id +'_date">'+ val.updated_date +'</h2>';
										new_html += '<ul data-role="listview" data-theme="d">';
										new_html += '<li>';
										new_html += '<div data-role="controlgroup" data-type="horizontal" style="position:relative">';
										new_html += '<a href="#" data-role="button" data-inline="true" data-theme="c" data-icon="check" class="mark" style="position:absolute; right:10px; top:-10px" data="'+ val.notice_id +'">Mark as Read</a>';
										new_html += '</div>';
										new_html += '</li>';
										new_html += '<li id="notice_'+ val.notice_id +'_summary">';
										new_html += val.notice_summary;
										new_html += '</li>';
										new_html += '</ul>';
										new_html += '</div>';
										$('#notice_content').append(new_html);						
									}

									if (val.notice_status == 'read') {
										var html = '<div data-role="collapsible" data-content-theme="c" data-theme="c" class="notice_list old-notice">';
										html += '<h2>'+ val.updated_date +'</h2>';
										html += '<ul data-role="listview" data-theme="d">';
										html += '<li>';
										html += val.notice_summary;
										html += '</li>';
										html += '</ul>';
										html += '</div>';
										$('#old_notice_content').append(html);
									}
								});

								if (!$('.new-notice').length > 0) {
									$('#notice_content').append('<p class="no-notices">No New Notices to Display</p>');
								}

								if (!$('.old-notice').length > 0) {
									$('#old_notice_content').append('<p class="no-old-notices">No Previously Read Notices to Display</p>');
								}

								$('#notice_content').trigger("create");
								$('#old_notice_content').trigger("create");

								$('.mark').bind('touchstart', function (e) {
									e.preventDefault();
									var notice_id = $(this).attr('data');
									$.ajax({
										url: 'http://' + window.url + '/api/mobile.api.php',
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
							}
						}, error: function (event, request, settings, exception, data) {

						} 
					});
				});
				
				$('#logout').bind('touchstart', function () {
					$$("scheduler").clearAll();
					$$("scheduler").$$("dayList").render();
					$('.new-notice, .old-notice').remove();
					$('.notices, .agenda').unbind('touchstart');

					if (!$('.new-notice').length > 0) {
						$('#notice_content').append('<p class="no-notices">No New Notices to Display</p>');
					}

					if (!$('.old-notice').length > 0) {
						$('#old_notice_content').append('<p class="no-old-notices">No Previously Read Notices to Display</p>');
					}
					
					if (window.localStorage.getItem('hash') != null) {
						window.localStorage.removeItem("hash");
					}
					
					$('.close').hide();
				});
			}
			
			function initScheduler () {
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

					$$("scheduler").$$("dayList").scrollTo(0,31*8);
					$$("scheduler").$$("buttons").setValue("day");
					$$("scheduler").$$("day").show();
					scheduler_size();
				});
			}

			function loadScheduler () {
				$.ajax({
					url: 'http://' + window.url + '/api/mobile.api.php',
					type: 'post',
					data: ({method:'agenda', username:username, password:password, hash:window.localStorage.getItem('hash')}),
					dataType: 'json',
					success:function (data) {
						$$("scheduler").clearAll();
						$$("scheduler").parse(data);
						$$("scheduler").$$("dayList").render();
					}, error: function (event, request, settings, exception, data) {
						navigator.notification.alert('An error occured while loading the calendar');
					} 
				});
			}

			function scheduler_size() {
				var width = $("html").width();
				var height = $("html").height() - 95;
				$("#content").css("height", height);
				$$("scheduler").define("width", width);
				$$("scheduler").define("height", height);
				$$("scheduler").resize();
				$(".dhx_dayevents_scale_event").css("width", width - 45);
				$(".dhx_dayevents_scale_event").children("div").each(function () {
					$(this).css("width", width - 44);
				});

				if ($$("scheduler").$$("buttons").getValue() =="day") {
					$$("scheduler").$$("dayList").render();
				}
			}
			
			function fetchHash () {
				$.ajax({
					url: 'http://' + window.url + '/api/mobile.api.php',
					type: 'post',
					data: ({method:'hash', username:username, password:password, hash:window.localStorage.getItem('hash')}),
					dataType: 'text',
					success:function (data) {
						window.localStorage.setItem("hash", data);
					}, error: function (event, request, settings, exception, data) {
						
					} 
				});
			}
			
			function hideUserInfo () {
				$('#username_field').hide();
				$('#password_field').hide();
				navigator.notifiaction.alert('fired hide');
			}
			
			function showUserInfo () {
				$('#username_field').show();
				$('#password_field').show();
				navigator.notifiaction.alert('fired show');
			}

			$(window).bind("resize", function () {
				scheduler_size();
			});
			
			$('#save-settings').bind('touchstart', function () {
				window.url = $('#url').val();
				username = $('#username').val();
				password = $('#password').val();
				$.ajax({
					url: 'http://' + window.url + '/api/mobile.api.php',
					type: 'post',
					data: ({method:'credentials', username:username, password:password}),
					dataType: 'text',
					success:function (data) {
						if (data == true) {
							$('.close').show();
							loadApp();
							$.mobile.changePage('#agenda', {transition: 'flip'});
							if ($('input[name=login-option]:checked').val() == '1') {
								fetchHash();
							} else if ($('input[name=login-option]:checked').val() == '0') {
								if (window.localStorage.getItem('hash') != null) {
									window.localStorage.removeItem("hash");
								}
							}
						} else {
							navigator.notification.alert('Invalid credentials');
						}
					}, error: function (event, request, settings, exception, data) {
						navigator.notification.alert('An error occured during authentication');
					} 
				});
			});
			
			/*if ($('#save-password').prop('checked', true)) {
				navigator.notification.alert('save checked');
			}
			
			if ($('#required-password').prop('checked', true)) {
				navigator.notification.alert('required checked');
			}*/
		}); 
    }
}