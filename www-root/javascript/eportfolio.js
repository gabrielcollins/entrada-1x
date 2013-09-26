jQuery(function($) {
	
	var pfolder_id = $("#folder-list").children(":first").children("a").data("id");
	
	if (location.hash.length > 0) {
		pfolder_id = parseInt(location.hash.substring(1, location.hash.length));
	}
	
	getFolder(pfolder_id);
	
	$(".add-entry").on("click", function(e) {
		var link = $(this);
		var entry_data = {
			pfartifact_id : link.attr("data-pfartifact-id"),
			description : "sadasd"
		}

		$.ajax({
			url : ENTRADA_URL + "/api/eportfolio.api.php",
			type : "POST",
			data : "method=create-entry&" + $.param(entry_data),
			success: function(data) {
				var jsonResponse = JSON.parse(data);
			}
		});
		
		e.preventDefault();
	});
	
	$("#create-artifact").on("click", function () {
		$(".modal-header h3").html("Create Artifact");
		$("#save-button").html("Save Artifact").attr("data-type", "artifact");
		artifactForm();
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
			url : ENTRADA_URL + "/api/eportfolio.api.php",
			type : "POST",
			data : "method=create-artifact&" + $.param(entry_data),
			success: function(data) {
				var jsonResponse = JSON.parse(data);
			}
		});
		
		e.preventDefault();
	});
	
	$(".add-folder").on("click", function(e) {
		var link = $(this);
		var folder_data = {
			portfolio_id : link.attr("data-portfolio-id"),
			title : "New Test Folder",
			description : "New Test Folder Description",
			allow_learner_artifacts : 1
		}
		
		$.ajax({
			url : ENTRADA_URL + "/api/eportfolio.api.php",
			type : "POST",
			data : "method=create-folder&" + $.param(folder_data),
			success: function(data) {
				var jsonResponse = JSON.parse(data);
			}
		});
		
		e.preventDefault();
	});
	
	$("#portfolio-form").on("submit", function(e) {
				
		if ($(".isie").length > 0) {
			$("#method").attr("value", "create-entry").attr("name", "method");
		} else {
			var xhr = new XMLHttpRequest();
			var fd = new FormData();
			var file = $("#media-entry-upload").prop("files");

			fd.append("method", "create-entry");
			fd.append("title", jQuery("#media-entry-title").val());
			fd.append("description", jQuery("#entry-description").val());
			fd.append("pfartifact_id", jQuery("#pfartifact_id").val());
			fd.append("type", "file");
			fd.append("file", file[0]);

			xhr.open('POST', ENTRADA_URL + "/api/eportfolio.api.php", true);
			xhr.send(fd);

			xhr.onreadystatechange = function() {
				if (xhr.readyState == 4 && xhr.status == 200) {
					var jsonResponse = JSON.parse(xhr.responseText);
					if (jsonResponse.status == "success") {
						
						var entry_row = document.createElement("tr");
						
						var entry_delete_cell = document.createElement("td");
						var entry_delete_button = document.createElement("button");
						$(entry_delete_button).addClass("btn btn-mini btn-danger").html("<i class=\"icon-trash icon-white\"></i>");
						$(entry_delete_cell).append(entry_delete_button);
						
						var entry_date_cell = document.createElement("td");
						var date = new Date(jsonResponse.data.submitted_date * 1000);
						$(entry_date_cell).html(date.getFullYear() + "-" + (date.getMonth() <= 9 ? "0" : "") + (date.getMonth() + 1) + "-" +  (date.getDate() <= 9 ? "0" : "") + date.getDate());
						
						var entry_content_cell = document.createElement("td");
						var content = "";
						if (typeof jsonResponse.data.edata.filename != "undefined") {
							content = "<a href=\"" + ENTRADA_URL + "/serve-eportfolio-entry.php?entry_id=" + jsonResponse.data.pentry_id + "\">" + jsonResponse.data.edata.filename + "</a>";
						} else {
							content = jsonResponse.data.edata.description.replace(/(<([^>]+)>)/ig,"").substr(0, 80) + "...";
						}
						$(entry_content_cell).append(content);
						
						$(entry_row).append(entry_delete_cell).append(entry_date_cell).append(entry_content_cell);
						
						$("#artifact-"+$("#pfartifact_id").val()).append(entry_row);
						
						$('#portfolio-modal').modal('hide');
						
					} else {
						// Some kind of failure notification.
					}
				} else {
					// another failure notification.
				}
			}
			e.preventDefault();
		}

		e.preventDefault();
	});

	$(".folder-item").on("click", function (e) {
		e.preventDefault();
		pfolder_id = $(this).data("id");
		getFolder(pfolder_id);
		location.hash = $(this).attr("data-id");
	});

	$("#save-button").on("click", function(e) {
		var button = $(this);
		var type = $(this).attr("data-type");
		var pfartifact_id = jQuery(this).attr("data-artifact");

		switch (type) {
			case "file" :
				if (window.FileReader) {

				} else {
					$("#portfolio-form").append("<input type=\"hidden\" name=\"isie\" value=\"isie\" class=\"isie\" />");
				}
				$("#portfolio-form").attr("enctype", "multipart/form-data").attr("action", ENTRADA_URL + "/api/eportfolio.api.php").submit();
			break;
			case "artifact" :
			case "reflection" :
				var method = (type == "reflection" || type == "file" ? "create-entry&pfartifact_id=" + pfartifact_id : "create-artifact&pfolder_id=" + pfolder_id);
				$.ajax({
					url : ENTRADA_URL + "/api/eportfolio.api.php",
					type : "POST",
					data : "method=" + method + "&type=" + type +  "&" + $("#portfolio-form").serialize(),
					success: function(data) {
						console.log(data);
						var jsonResponse = JSON.parse(data);
						if (jsonResponse.status == "success") {
							appendContent(type, jsonResponse.data, pfartifact_id);
							$("#portfolio-modal").modal("hide");
						} else {
							var msgs = new Array();
							display_error(jsonResponse.data, "#modal-msg");
						}
					}
				});
			break;
		}

		e.preventDefault();
	});

	jQuery("#portfolio-modal").on("hide", function () {
		if (jQuery("#entry-description").length && jQuery("#entry-description").hasClass("reflection")) {
			jQuery('#entry-description').ckeditorGet().destroy();
		}
		
		if ($("#portfolio-form .control-group").length) {
			$(".control-group").remove();
		}

		if ($("#display-error-box-modal")) {
			$("#modal-msg").empty();
		}
	});
});

function getFolder (pfolder_id) {
	jQuery.ajax({
		url: ENTRADA_URL + "/api/eportfolio.api.php",
		data: "method=get-folder&pfolder_id=" + pfolder_id,
		type: 'GET',
		success:function (data) {
			var jsonResponse = JSON.parse(data);
			if (jsonResponse.status === "success") {
				jQuery("#folder-title").html(jsonResponse.data.title);
				getFolderArtifacts(pfolder_id);
			}
		}	
	});
}

function getFolderArtifacts (pfolder_id) {
	var proxy_id = PROXY_ID;
	jQuery.ajax({
		url: ENTRADA_URL + "/api/eportfolio.api.php",
		data: "method=get-folder-artifacts&pfolder_id=" + pfolder_id + "&proxy_id=" + proxy_id,
		type: 'GET',
		success:function (data) {
			var jsonResponse = JSON.parse(data);
			jQuery(".artifact-container").empty();
			if (jsonResponse.status == "success") {
				if (jQuery("#display-notice-box-modal").length) {
					jQuery("#msgs").empty();
				}

				jQuery.each(jsonResponse.data, function (key, artifact) {
					var pfartifact_id = artifact.pfartifact_id;
					var artifact_title = artifact.title;
					appendArtifact(pfartifact_id, artifact_title);
					getEntries(pfartifact_id);
				});

			} else {
				var msgs = new Array();
				msgs[0] = jsonResponse.data;
				display_notice(msgs, "#msgs");
			}
		}	
	});
}

function getEntries (pfartifact_id) {
	var proxy_id = PROXY_ID;
	jQuery.ajax({
		url: ENTRADA_URL + "/api/eportfolio.api.php",
		data: "method=get-artifact-entries&pfartifact_id=" + pfartifact_id + "&proxy_id=" + proxy_id,
		type: 'GET',
		success:function (data) {
			var jsonResponse = JSON.parse(data);
			if (jsonResponse.status == "success") {
				jQuery.each(jsonResponse.data, function(key, entry) {
					// Create row and cells for each entry
					var entry_row = document.createElement("tr");
					var delete_td = document.createElement("td");
					var entry_date_td = document.createElement("td");
					var entry_content_td = document.createElement("td");
					var entry_date_a = document.createElement("a");
					var entry_content_a = document.createElement("a");

					// Append the date to the date cell
					jQuery(entry_date_a).html(format_date(entry.submitted_date, "yyyy-mm-dd")).attr({"href": "#", "data-toggle": "modal", "data-target": "#portfolio-modal", "data-type": entry.type + "-entry"}).addClass("edit-entry");
					jQuery(entry_date_td).append(entry_date_a);

					// Check to see if the _edata object has a description or filename and put the data in the content cell
					if (entry._edata.hasOwnProperty("description")) {
						
						var description = "";
						if (entry._edata.description != null) {
							description = entry._edata.description.replace(/(<([^>]+)>)/ig,"").substr(0, 80) + "...";
						}
						jQuery(entry_content_a).html(description).attr({"href": "#", "data-toggle": "modal", "data-target": "#portfolio-modal", "data-type": entry.type + "-entry"}).addClass("edit-entry");
						jQuery(entry_content_td).append(entry_content_a);
					} 

					if (entry._edata.hasOwnProperty("filename")) {
						jQuery(entry_content_a).html(entry._edata.filename);
						jQuery(entry_content_td).append(entry_content_a);
					}

					// Create delete button and icon
					var delete_button = document.createElement("button");
					jQuery(delete_button).addClass("btn btn-mini btn-danger");

					var delete_icon = document.createElement("i");
					jQuery(delete_icon).addClass("icon-trash icon-white");

					// Append the icon to the button and then append the button to the delete cell
					jQuery(delete_button).append(delete_icon);								
					jQuery(delete_td).append(delete_button);

					// Append cells to the enrty row
					jQuery(entry_row).append(delete_td).append(entry_date_td).append(entry_content_td).attr("data-id", entry.pentry_id);

					// Append entry row to appropriate artifact
					jQuery("#artifact-" + entry.pfartifact_id).append(entry_row);
				});
				
				jQuery("#artifact-" + pfartifact_id).on("click", ".edit-entry", function (e) {
					e.preventDefault();
					var pentry_id = jQuery(this).parent().parent().data("id");
					jQuery("#method").attr("value", jQuery(this).data("type"));
					jQuery("#save-button").attr("data-type", "edit-" + jQuery(this).data("type"));
					populateEntryForm(pfartifact_id, pentry_id);
				});
			} else {
				// Create error row and cell
				var error_row = document.createElement("tr");
				var error_cell = document.createElement("td");
				jQuery(error_cell).append(jsonResponse.data).attr("colspan", "3");
				jQuery(error_row).append(error_cell).addClass("no-entries");
				jQuery("#artifact-" + pfartifact_id).append(error_row);
			}
		}	
	});
}

function artifactForm () {
	// Create the divs that will hold the form controls for the create artifact form
	var title_control_group = document.createElement("div");
	jQuery(title_control_group).addClass("control-group");
	var title_controls = document.createElement("div");
	jQuery(title_controls).addClass("controls");
	var description_control_group = document.createElement("div");
	jQuery(description_control_group).addClass("control-group");
	var description_controls = document.createElement("div");
	jQuery(description_controls).addClass("controls");

	// Create the form controls
	var title_input = document.createElement("input");
	jQuery(title_input).attr({type: "text", name: "title", id: "artifact-title"}).addClass("input-large");

	var description_textarea = document.createElement("textarea");
	jQuery(description_textarea).attr({name: "description", id: "artifact-description"}).addClass("input-large");

	// Create the labels for the create artifact form controls
	var title_label = document.createElement("label");
	jQuery(title_label).html("Title:").attr("for", "artifact-title").addClass("control-label");
	var description_label = document.createElement("label");
	jQuery(description_label).html("Description:").attr("for", "artifact-description").addClass("control-label");

	// Put it all together
	jQuery(title_controls).append(title_input);
	jQuery(title_control_group).append(title_label).append(title_controls);
	jQuery(description_controls).append(description_textarea);
	jQuery(description_control_group).append(description_label).append(description_controls);
	jQuery("#portfolio-form").append(title_control_group).append(description_control_group);
}

function entryForm (pfartifact_id) {
	var pfartifact_id_input = document.createElement("input");
	jQuery(pfartifact_id_input).attr({value: pfartifact_id, name: "pfartifact_id", id: "pfartifact_id", type: "hidden"});

	// Create the divs that will hold the form controls for the create artifact form
	var title_control_group = document.createElement("div");
	jQuery(title_control_group).addClass("control-group");
	var title_controls = document.createElement("div");
	jQuery(title_controls).addClass("controls");

	// Create the form controls
	var title_input = document.createElement("input");
	jQuery(title_input).attr({type: "text", name: "title", id: "media-entry-title"}).addClass("input-large");


	// Create the labels for the create artifact form controls
	var title_label = document.createElement("label");
	jQuery(title_label).html("Title:").attr("for", "media-entry-title").addClass("control-label");


	// Put it all together
	jQuery(title_controls).append(title_input);
	jQuery(title_control_group).append(title_label).append(title_controls);

	var entry_control_group = document.createElement("div");
	jQuery(entry_control_group).addClass("control-group");
	var entry_controls = document.createElement("div");
	jQuery(entry_controls).addClass("controls");
	var entry_label = document.createElement("label");
	jQuery(entry_label).addClass("control-label");

	// Add appropriate form controls depending on the selected content type
	var method = jQuery("#method").val();
	switch (method) {
		
		case "file-entry" :
			var description_control_group = document.createElement("div");
			jQuery(description_control_group).addClass("control-group");
			var description_controls = document.createElement("div");
			jQuery(description_controls).addClass("controls");
			var description_textarea = document.createElement("textarea");
			jQuery(description_textarea).attr({name: "description", id: "entry-description"}).addClass("input-large");
			var description_label = document.createElement("label");
			jQuery(description_label).html("Description:").attr("for", "entry-description").addClass("control-label");
			jQuery(description_controls).append(description_textarea);
			jQuery(description_control_group).append(description_label).append(description_controls);
			if (jQuery("#save-button").attr("data-type") == "edit-file-entry") {
				jQuery(entry_label).html("File Name:").attr("for", "media-entry-upload");
				var entry_input = document.createElement("label");
				jQuery(entry_input).attr({id: "media-entry-upload", name: "file"}).addClass("control-label");
			} else {
				jQuery(entry_label).html("Attach File:").attr("for", "media-entry-upload");
				var entry_input = document.createElement("input");
				jQuery(entry_input).attr({type: "file", id: "media-entry-upload", name: "file"});
			}
			
		break;
		case "reflection-entry" :
			jQuery(entry_label).html("Reflection Body:").attr("for", "reflection-entry");
			var entry_input = document.createElement("textarea");
			jQuery(entry_input).attr({id: "entry-description", name: "description", "class": "reflection"});
		break;
	}
	jQuery(entry_controls).append(entry_input);
	jQuery(entry_control_group).append(entry_label).append(entry_controls);
	jQuery("#portfolio-form").append(title_control_group).append(description_control_group).append(entry_control_group).append(pfartifact_id_input);
	if (jQuery("#entry-description").hasClass("reflection")) {
		jQuery("#entry-description").ckeditor();
	}
}

function appendArtifact (pfartifact_id, artifact_title) {
	var folder_artifact = document.createElement("div");
	var artifact_heading = document.createElement("h3");
	jQuery(artifact_heading).html(artifact_title);

	// Create the artifact meta data paragraph
	/*
	var artifact_meta = document.createElement("p");
	jQuery(artifact_meta).html((artifact.start_date > 0 ? "Created on: <strong>" + format_date(artifact.start_date) + "</strong>, " : "") + (artifact.finish_date > 0 ? "Due on: <strong>" + format_date(artifact.finish_date) + "</strong>" : "" )).addClass("muted");
	*/

	// Create the button group for artifact Content 
	var artifact_content = document.createElement("div");
	jQuery(artifact_content).addClass("btn-group pull-right space-below");
	var artifact_options_button = document.createElement("button");
	jQuery(artifact_options_button).addClass("btn btn-primary dropdown-toggle").attr("data-toggle", "dropdown").html("Add Content ");
	var artifact_options_span = document.createElement("span");
	jQuery(artifact_options_span).addClass("caret");

	// Append the the artifact content button to the button group
	jQuery(artifact_options_button).append(artifact_options_span);
	jQuery(artifact_content).append(artifact_options_button);

	// Create the options list for the artifact content button group
	var artifact_options = document.createElement("ul");
	jQuery(artifact_options).addClass("dropdown-menu");

	// Create list items and links
	var artifact_option_reflection = document.createElement("li");
	var artifact_option_media = document.createElement("li");
	var artifact_option_reflection_a = document.createElement("a");
	jQuery(artifact_option_reflection_a).html("Reflection");
	jQuery(artifact_option_reflection_a).attr("data-toggle", "modal").attr("data-target", "#portfolio-modal").addClass("reflection-content");
	var artifact_option_media_a = document.createElement("a");
	jQuery(artifact_option_media_a).html("Media");
	jQuery(artifact_option_media_a).attr("data-toggle", "modal").attr("data-target", "#portfolio-modal").addClass("media-content");

	// Attach click event to reflection and media links to update the contents of the portfolio-modal
	jQuery(artifact_option_reflection_a).on("click", function (e) {
		e.preventDefault();
		jQuery("#method").attr("value", "reflection-entry");
		jQuery(".modal-header h3").html("Add Reflection");
		jQuery("#save-button").html("Save Reflection").attr("data-type", "reflection");
		jQuery("#save-button").attr("data-artifact", pfartifact_id);
		entryForm(pfartifact_id);
	});

	jQuery(artifact_option_media_a).on("click", function (e) {
		e.preventDefault();
		jQuery("#method").attr("value", "file-entry");
		jQuery(".modal-header h3").html("Add Media");
		jQuery("#save-button").html("Save Media").attr("data-type", "file");
		jQuery("#save-button").attr("data-artifact", pfartifact_id);
		entryForm(pfartifact_id);
	});

	// Append anchors to list items 
	jQuery(artifact_option_reflection).append(artifact_option_reflection_a);
	jQuery(artifact_option_media).append(artifact_option_media_a);

	// Append list items to artifact options ul
	jQuery(artifact_options).append(artifact_option_media);
	jQuery(artifact_options).append(artifact_option_reflection);

	// Append the artifact options ul to the button group
	jQuery(artifact_content).append(artifact_options);

	var artifact_row = document.createElement("div");
	jQuery(artifact_row).addClass("row-fluid");

	var artifact_entries = document.createElement("div");
	jQuery(artifact_entries).addClass("span12");

	var entries_table = document.createElement("table");
	jQuery(entries_table).addClass("table table-striped table-bordered");
	jQuery(entries_table).attr("id", "artifact-" + pfartifact_id);

	var entries_thead = document.createElement("thead");
	jQuery(entries_table).append(entries_thead);

	var entries_thead_row = document.createElement("tr");
	jQuery(entries_thead).append(entries_thead_row);

	var entries_title_th =  document.createElement("th");
	jQuery(entries_title_th).width("5%");
	jQuery(entries_thead_row).append(entries_title_th);

	var entries_date_th =  document.createElement("th");
	jQuery(entries_date_th).width("25%");
	jQuery(entries_date_th).html("Submitted Date");
	jQuery(entries_thead_row).append(entries_date_th);

	var entries_content_th =  document.createElement("th");
	jQuery(entries_content_th).html("Content");
	jQuery(entries_thead_row).append(entries_content_th);

	jQuery(artifact_entries).append(entries_table);
	jQuery(artifact_row).append(artifact_entries);
	jQuery(folder_artifact).addClass("artifact").append(artifact_heading).append(artifact_content).append(artifact_row);
	jQuery(".artifact-container").append(folder_artifact);
}

function appendContent (type, jsonResponse, pfartifact_id) {
	if (jQuery("#display-notice-box-modal").length) {
		jQuery("#msgs").empty();
	}
	switch (type) {
		case "artifact" :
			var artifact_title = jQuery("#artifact-title").val();
			appendArtifact(pfartifact_id, jsonResponse.title);
			var entry_row = document.createElement("tr");
			var entry_cell = document.createElement("td");
			jQuery(entry_cell).append("No entries attached to this artifact.").attr("colspan", "3");
			jQuery(entry_row).append(entry_cell);
			jQuery("#artifact-" + pfartifact_id).append(entry_row);
		break;
		case "reflection" :
			var entry_row = document.createElement("tr");			
			var entry_delete_cell = document.createElement("td");
			var entry_delete_button = document.createElement("button");
			var entry_date_a =  document.createElement("a");
			var entry_content_a =  document.createElement("a");
			jQuery(entry_delete_button).addClass("btn btn-mini btn-danger").html("<i class=\"icon-trash icon-white\"></i>");
			jQuery(entry_delete_cell).append(entry_delete_button);

			var entry_date_cell = document.createElement("td");
			var date = new Date(jsonResponse.submitted_date * 1000);
			jQuery(entry_date_a).html(date.getFullYear() + "-" + (date.getMonth() <= 9 ? "0" : "") + (date.getMonth() + 1) + "-" +  (date.getDate() <= 9 ? "0" : "") + date.getDate()).attr({"href": "#", "data-toggle": "modal", "data-target": "#portfolio-modal", "data-type": type + "-entry"}).addClass("edit-entry");
			jQuery(entry_date_cell).append(entry_date_a);

			var entry_content_cell = document.createElement("td");
			if (jsonResponse.edata.description.length > 80) {
				content = jsonResponse.edata.description.replace(/(<([^>]+)>)/ig,"").substr(0, 80) + "...";
			} else {
				content = jsonResponse.edata.description;
			}
			jQuery(entry_content_a).html(content).attr({"href": "#", "data-toggle": "modal", "data-target": "#portfolio-modal", "data-type": type + "-entry"}).addClass("edit-entry");
			jQuery(entry_content_cell).append(entry_content_a);
			jQuery(entry_row).append(entry_delete_cell).append(entry_date_cell).append(entry_content_cell).attr("data-id", jsonResponse.pentry_id);
			jQuery("#artifact-" + pfartifact_id).append(entry_row);
			if (jQuery("#artifact-" + pfartifact_id + " .no-entries").length) {
				jQuery(".no-entries").remove();
			}
		break;
		case "media" :
		break;
	}
}

function populateEntryForm(pfartifact_id, pentry_id) {
	entryForm(pfartifact_id);
	jQuery.ajax({
		url: ENTRADA_URL + "/api/eportfolio.api.php",
		data: "method=get-entry&pentry_id=" + pentry_id,
		type: 'GET',
		async: false,
		success:function (data) {
			var jsonResponse = JSON.parse(data);
			if (jsonResponse.status === "success") {
				jQuery("#media-entry-title").val(jsonResponse.data._edata.title);
				jQuery("#entry-description").val(jsonResponse.data._edata.description);
				switch (jsonResponse.data.type) {
					case "reflection" :
						jQuery(".modal-header h3").html("Edit Reflection");
					break;
					case "file" :
						jQuery(".modal-header h3").html("Edit Media");
						jQuery("#media-entry-upload").html(jsonResponse.data._edata.filename);
						var control_group = document.createElement("div");
						jQuery(control_group).addClass("control-group");
						var controls = document.createElement("div");
						jQuery(controls).addClass("controls");
						var download_label = document.createElement("label");
						jQuery(download_label).css("width", "150px").addClass("control-label");
						var file_download_a = document.createElement("a");
						jQuery(file_download_a).html("<i class=\"icon-download-alt icon-white\"></i> Download File").attr("href", ENTRADA_URL + "/serve-eportfolio-entry.php?entry_id=" + jsonResponse.data.pentry_id).addClass("btn btn-success");
						jQuery(controls).append(file_download_a);
						jQuery(control_group).append(download_label).append(controls);
						jQuery("#portfolio-form").append(control_group);
					break;
				}
			}
		}	
	});
	
}