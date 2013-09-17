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
		})
		
		e.preventDefault();
	});
});