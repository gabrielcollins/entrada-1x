/*
 * Feedback sidebar widget
 */

var shown = false;

function submitFeedback() {
	return true;
}

function removeFeedbackForm() {
	jQuery("#feedback-form-container").slideUp(function(){
		jQuery(this).remove()
	});
	shown = false;
}

jQuery(function(){
	jQuery("#feedback-widget").parent().css("overflow","visible");

	jQuery("#feedback-widget .menu li a").live("click", function(){
		if (shown == false) {
			shown = true;
			
			var who = jQuery(this).parent().attr("class");
			var container = jQuery("<div id=\"feedback-form-container\" />");
			var url = jQuery(this).attr("href");
			jQuery.ajax({
				url: url,
				type: 'POST',
				data: "who="+who+"&enc="+jQuery("#feedback-widget ul.feedback").attr("data-enc"),
				async: false,
				success: function (data) {
					container.html("<div class='cornerarrow'></div>"+data);
				}
			});

			container.appendTo(jQuery("#feedback-widget"));
			jQuery("#feedback-form-container").animate({
				left: jQuery(this).parent().width() + 22 + 34
			}, 200);
			
		} else {
			removeFeedbackForm();
		}
		return false;
	});
	jQuery("#feedback-form input[value=Close]").live("click", function() {
		removeFeedbackForm()
		return false;
	});
	jQuery("#feedback-form input[value=Submit]").live("click", function() {
		var action		= jQuery("#feedback-form").attr("action");
		var method		= jQuery("#feedback-form").attr("method");
		var form_data	= jQuery("#feedback-form").serialize();

		jQuery.ajax({
			url: action,
			type: method,
			data: form_data,
			async: false,
			success: function (data) {
				jQuery("#wizard-body, #wizard-footer").fadeOut(500, function(){
					jQuery("#wizard-body").html(data);
					jQuery("#wizard-body").fadeIn();
					setTimeout("removeFeedbackForm()", 5000);
				});
			}
		});

		return false;
	});
});