jQuery(function($) {
	$(".add-entry").on("click", function(e) {
		var link = $(this);
		var entry_data = {
			pfartifact_id : link.attr("data-pfartifact-id"),
			description : "sadasd"
		}

		$.ajax({
			url: ENTRADA_URL + "/api/eportfolio.api.php",
			type: "POST",
			data: "method=create-entry&" + $.param(entry_data),
			success: function(data) {
				var jsonResponse = JSON.parse(data);
			}
		});
		
		e.preventDefault();
	});
	
	$(".add-artifact").on("click", function(e) {
		var link = $(this);
		var entry_data = {
			pfolder_id : link.attr("data-pfolder-id"),
			description : "<p>Hello this is an artifact description, it is something about things.</p>",
			title : "This is the test artifact title!",
			start_date : "2013-09-20",
			finish_date : "2013-09-27"
		}

		$.ajax({
			url: ENTRADA_URL + "/api/eportfolio.api.php",
			type: "POST",
			data: "method=create-artifact&" + $.param(entry_data),
			success: function(data) {
				var jsonResponse = JSON.parse(data);
			}
		});
		
		e.preventDefault();
	});
});