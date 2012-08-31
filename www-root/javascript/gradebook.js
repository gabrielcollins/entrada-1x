var ENTRADA_URL;

jQuery(document).ready(function($) {
	var loading_html = '<img width="16" height="16" src="'+ENTRADA_URL+'/images/loading.gif">';
	
	var flexiopts = {
		resizable: false,
		height: 'auto',
		disableSelect: true,
		showToggleBtn: false
	};
	
	var gradebookize = function() {
		$('table.gradebook.single').flexigrid($.extend({}, flexiopts, {
			width: 440,
			colModel: [
				{display: 'Student Name', name: 'name', width: 160, sortable: false},
				{display: 'Student Number', name: 'number', width: 140, sortable: false},
				{display: 'Student Mark', name: 'name', width: 102, sortable: false},
			]
		}));

		$('table.gradebook.numeric').flexigrid($.extend({}, flexiopts, {
			width: 440,
			colModel: [
				{display: 'Student Name', name: 'name', width: 150, sortable: false},
				{display: 'Student Number', name: 'number', width: 100, sortable: false},
				{display: 'Student Mark', name: 'name', width: 100, sortable: false},
				{display: 'Percent', name: 'name', width: 40, sortable: false}
			]
		}));

		$('table.gradebook.assignment').flexigrid($.extend({}, flexiopts, {
			colModel: [

			]
		}));

		$('table.gradebook').flexigrid($.extend({}, flexiopts, {
			title: "Gradebook",
			buttons : [
				{name: "Close", bclass: "gradebook_edit_close"},
				{name: "Add Assessment", bclass: "gradebook_edit_add"},
				{separator: true},
				{name: "Change Grad Year", bclass: "change_gradebook_year"}
			]
		}));

		$('.change_gradebook_year').html( $('#toolbar').html() ).css('margin', '-3px');
		$('.change_gradebook_year select').change(function(e) {
			$('.gradebook_edit').html(loading_html);
			$.ajax({
				url: $('#fullscreen-edit').attr('href') + "&cohort=" + $(this).val(),
				cache: false, 
				success: function(data, status, request) {
					$('.gradebook_edit').html(data);
					gradebookize();
				},
				error: function()  {
					alert("Error loading the new gradebook to edit! Please refresh the page.");
				}
			});
		});

		$('table.gradebook.gradebook_editable .grade').editable(ENTRADA_URL+'/api/gradebook.api.php', {
			placeholder: '-',
			indicator: loading_html,
			onblur: 'submit',
			width: 40,
			cssclass: 'editing',
			onsubmit: function(settings, original) {
			},
			submitdata: function(value, settings) {
				return {
					grade_id: $(this).attr('data-grade-id'),
					assessment_id: $(this).attr('data-assessment-id'),
					proxy_id: $(this).attr('data-proxy-id')
				};
			},
			callback: function(value, settings) {
				// If grade came back deleted remove the grade ID data
				if(value != "-") {
					var values = value.split("|");
					var grade_id = values[0];
					value = values[1];
					$(this).html(value);				
				}
					
				var suffix = $(this).next('.gradesuffix').html().split('|');

				if(value == "-") {
					var percent = 0;
					$(this).attr('data-grade-id', '');
					$(this).next('.gradesuffix').hide();
				} else {
					if (suffix[1]) {
						var percent = (value/suffix[1]*100).toFixed(2);
					}
					
					$(this).attr('data-grade-id', grade_id);
					$(this).next('.gradesuffix').show();
				}
				
				if (suffix[1]) {
					var id_suffix = $(this).attr('id').substring(5);
					$('#percentage'+id_suffix).html('<div style="width: 45px; ">'+percent+'%</div>');
				}				
			}
		}).keyup(function(e){
			var dest;
			
			switch(e.which) {
				case 38: // Up
				case 40: // Down
				case 13: // Enter
					// Go up or down a line
					$('input', this).trigger('blur');
					var pos = $(this).parent().parent().prevAll().length;
					var row = $(this).parent().parent().parent();
					if(e.which == 38) { //going up!
						dest = row.prev();
						if($(dest).attr('class').indexOf('comment-row') !== -1){
							dest = dest.prev();
						}
					} else {
						dest = row.next();
						if($(dest).attr('class').indexOf('comment-row') !== -1){
							dest = dest.next();
						}
					}

					if(dest) {
						var next = dest.children()[pos];
						if(next) {
							next = $(next).find('.grade');
						}
					}

					$(next).trigger('click');
				break;
				default:
				break;
			}
		});
	};	
	
	gradebookize();
	
	$('.gradebook_edit').jqm({
		ajax: '@href',
		ajaxText: loading_html,
		trigger: $("#fullscreen-edit"),
		modal: true,
		toTop: true,
		overlay: 100,
		onShow: function(hash) {
			hash.w.show();
		},
		onLoad: function(hash) {
			gradebookize();
			
		},
		onHide: function(hash) {
			hash.o.hide();
			hash.w.hide();
		}
	});
	
	$("#fullscreen-edit").click(function(e) {
		e.preventDefault();
		$('#navigator-container').hide();
	});
	
	$('.gradebook_edit_close').live('click', function(e) {
		e.preventDefault();
		$('#navigator-container').show();
		$('.gradebook_edit').jqmHide();
	});
	
	$('.gradebook_edit_add').live('click', function(e) {
		window.location = $("#gradebook_assessment_add").attr('href');
	});
});