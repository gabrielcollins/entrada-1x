jQuery(function($) {
	
	var pfolder_id = $("#folder-list").children(":first").children("a").data("id");
	
	if (location.hash.length > 0) {
		pfolder_id = parseInt(location.hash.substring(1, location.hash.length));
	}
	
	getFolder(pfolder_id);
	
	$("#create-artifact").on("click", function () {
		$(".modal-header h3").html("Create Artifact");
		$("#save-button").html("Save Artifact").attr("data-type", "artifact");
		artifactForm();
	});
	
	$("#portfolio-form").on("submit", function(e) {
				
		if ($(".isie").length > 0) {
			$("#method").attr("value", "create-entry").attr("name", "method");
		} else {
			var xhr = new XMLHttpRequest();
			var fd = new FormData();
			var file = $("#media-entry-upload").prop("files");
			var pfartifact_id = jQuery("#save-button").data("artifact");

			fd.append("method", "create-entry");
			fd.append("title", jQuery("#media-entry-title").val());
			fd.append("description", jQuery("#entry-description").val());
			fd.append("pfartifact_id", pfartifact_id);
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
						jQuery(entry_delete_button).addClass("btn btn-mini btn-danger").html("<i class=\"icon-trash icon-white\"></i>");
						jQuery(entry_delete_cell).append(entry_delete_button);

						var entry_title_cell = document.createElement("td");
						var entry_title_a = document.createElement("a");
						jQuery(entry_title_a).html(jsonResponse.data.edata.title).attr({"href": "#", "data-toggle": "modal", "data-target": "#portfolio-modal", "data-type": jsonResponse.data.type + "-entry"}).addClass("edit-entry");
						jQuery(entry_title_cell).append(entry_title_a).addClass("entry-title");

						var entry_date_cell = document.createElement("td");
						var entry_date_a = document.createElement("a");
						var date = new Date(jsonResponse.data.submitted_date * 1000);
						jQuery(entry_date_a).html(date.getFullYear() + "-" + (date.getMonth() <= 9 ? "0" : "") + (date.getMonth() + 1) + "-" +  (date.getDate() <= 9 ? "0" : "") + date.getDate()).attr({"href": "#", "data-toggle": "modal", "data-target": "#portfolio-modal", "data-type": jsonResponse.data.type + "-entry"}).addClass("edit-entry");
						jQuery(entry_date_cell).append(entry_date_a).addClass("entry-date");

						var entry_content_cell = document.createElement("td");
						var entry_content_a = document.createElement("a");
						var content = "";
						if (typeof jsonResponse.data.edata.filename != "undefined") {
							content = jsonResponse.data.edata.filename;
						} else {
							content = jsonResponse.data.edata.description.replace(/(<([^>]+)>)/ig,"").substr(0, 80) + "...";
						}
						jQuery(entry_content_a).append(content).attr({"href": "#", "data-toggle": "modal", "data-target": "#portfolio-modal", "data-type": jsonResponse.data.type + "-entry"}).addClass("edit-entry");
						jQuery(entry_content_cell).append(entry_content_a).addClass("entry-content");

						jQuery(entry_row).append(entry_delete_cell).append(entry_title_cell).append(entry_content_cell).append(entry_date_cell).attr({"data-id": jsonResponse.data.pentry_id, "data-artifact": pfartifact_id});

						jQuery("#artifact-"+ pfartifact_id).append(entry_row);

						jQuery('#portfolio-modal').modal('hide');
						if (jQuery("#artifact-" + pfartifact_id + " .no-entries").length) {
							jQuery(".no-entries").remove();
						}
						
					} else {
						display_error(jsonResponse.data, "#msgs", "append");
					}
				} else {
					display_error(["The AJAX request did not properly complete."], "#msgs", "append");
				}
			}
			e.preventDefault();
		}

		e.preventDefault();
	});

	$(".folder-item").on("click", function (e) {
		
		$(".artifact-container").empty().addClass("loading");
		
		pfolder_id = $(this).data("id");
		getFolder(pfolder_id);
		location.hash = $(this).attr("data-id");
		
		e.preventDefault();
	});

	$("#save-button").on("click", function(e) {
		var button = $(this);
		var type = $(this).attr("data-type");
		var pfartifact_id =  jQuery("#save-button").attr("data-artifact");
		var method;
		
		switch (type) {
			case "file" :
				if (!window.FileReader) {
					$("#portfolio-form").append("<input type=\"hidden\" name=\"isie\" value=\"isie\" class=\"isie\" />");
				}
				$("#portfolio-form").attr("enctype", "multipart/form-data").attr("action", ENTRADA_URL + "/api/eportfolio.api.php").submit();
			break;
			case "reflection" :
				method = "create-entry&pfartifact_id=" + pfartifact_id;
			break;
			case "file-edit" :
				type = "file";
				method = method + "&filename=" + jQuery("#media-entry-upload").html();
			break;
			case "artifact" :
				method = "create-artifact&pfolder_id=" + pfolder_id;
			break;
			case "artifact-edit" :
				method = "create-artifact&pfolder_id=" + pfolder_id + "&pfartifact_id=" + pfartifact_id;
			break;
			case "url" :
				method = "create-entry&pfartifact_id=" + pfartifact_id;
			break;
		}
		
		if (jQuery("#save-button").attr("data-entry")) {
			var pentry_id = jQuery("#save-button").attr("data-entry");
			method =  method + "&pentry_id=" + pentry_id;
		}

		$.ajax({
			url: ENTRADA_URL + "/api/eportfolio.api.php",
			type: "POST",
			data: "method=" + method + "&type=" + type +  "&" + $("#portfolio-form").serialize(),
			success: function (data) {
				var jsonResponse = JSON.parse(data);
				if (jsonResponse.status == "success") {
					appendContent(type, jsonResponse.data, pfartifact_id);
					$("#portfolio-modal").modal("hide");
				} else {
					var msgs = new Array();
					display_error(jsonResponse.data, "#modal-msg");
				}
			},
			error: function (data) {
				display_error(["An error occurred while attempting save this entry. Please try again."], "#modal-msg");
			}
		});

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
	
	jQuery(".artifact-container").on("click", ".edit-entry", function (e) {
		e.preventDefault();
		var pfartifact_id = jQuery(this).parent().parent().data("artifact");
		var pentry_id = jQuery(this).parent().parent().data("id");
		jQuery("#method").attr("value", jQuery(this).data("type"));
		jQuery("#save-button").attr("data-action", "edit");
		jQuery("#save-button").attr("data-entry", pentry_id);
		jQuery("#save-button").attr("data-artifact", pfartifact_id);
		switch (jQuery(this).data("type")) {
			case "reflection-entry":
				jQuery("#save-button").attr("data-type", "reflection");
			break;
			case "file-entry":
				jQuery("#save-button").attr("data-type", "file-edit");
			break;
			case "url-entry":
				jQuery("#save-button").attr("data-type", "url");
			break;
		}
		populateEntryForm(pfartifact_id, pentry_id);
	});
	
	jQuery(".artifact-container").on("click", ".entry", function (e) {
		e.preventDefault();
		var pfartifact_id = jQuery(this).parent().parent().data("artifact");
		
		if (jQuery("#save-button").attr("data-action") || jQuery("#save-button").attr("data-entry")) {
			jQuery("#save-button").removeAttr("data-action");
			jQuery("#save-button").removeAttr("data-entry");
		}

		jQuery(".modal-header h3").html("Add Entry");
		
		if (jQuery(this).data("type") == "reflection") {
			jQuery("#save-button").html("Save Entry").attr("data-type", "reflection");
			jQuery("#method").attr("value", "reflection-entry");
		}
		
		if (jQuery(this).data("type") == "file") {
			jQuery("#save-button").html("Save Entry").attr("data-type", "file");
			jQuery("#method").attr("value", "file-entry");
		}
		
		if (jQuery(this).data("type") == "url") {
			jQuery("#save-button").html("Save Entry").attr("data-type", "url");
			jQuery("#method").attr("value", "url-entry");
		}
		
		jQuery("#save-button").attr("data-artifact", pfartifact_id);
		entryForm(pfartifact_id);
	});
	
	jQuery(".artifact-container").on("click", ".edit-artifact", function () {
		jQuery("#save-button").attr("data-type", "artifact-edit");
		var pfartifact_id = jQuery(this).data("artifact");
		jQuery("#save-button").attr("data-artifact", pfartifact_id);
		populateArtifactForm(pfartifact_id);
	});
});

function getFolder (pfolder_id) {
	jQuery.ajax({
		url: ENTRADA_URL + "/api/eportfolio.api.php",
		data: "method=get-folder&pfolder_id=" + pfolder_id,
		type: 'GET',
		success: function (data) {
			var jsonResponse = JSON.parse(data);
			if (jsonResponse.status === "success") {
				jQuery("#folder-title").html(jsonResponse.data.title);
				getFolderArtifacts(pfolder_id);
			} else {
				display_error(jsonResponse.data, "#msgs", "append");
			}
		},
		error: function (data) {
			jQuery(".artifact-container").removeClass("loading");
			display_error(["An error occurred while attempting to fetch this folder. Please try again."], "#msgs", "append");
		}
	});
}

function getFolderArtifacts (pfolder_id) {
	var proxy_id = PROXY_ID;
	jQuery.ajax({
		url: ENTRADA_URL + "/api/eportfolio.api.php",
		data: "method=get-folder-artifacts&pfolder_id=" + pfolder_id + "&proxy_id=" + proxy_id,
		type: 'GET',
		success: function (data) {
			jQuery(".artifact-container").removeClass("loading");
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
				display_notice([jsonResponse.data], "#msgs");
			}
		},
		error: function (data) {
			jQuery(".artifact-container").removeClass("loading");
			display_error(["An error occurred while attempting to fetch the artifacts associated with this folder. Please try again."], "#msgs", "append");
		}
	});
}

function getEntries (pfartifact_id) {
	var proxy_id = PROXY_ID;
	jQuery.ajax({
		url: ENTRADA_URL + "/api/eportfolio.api.php",
		data: "method=get-artifact-entries&pfartifact_id=" + pfartifact_id + "&proxy_id=" + proxy_id,
		type: 'GET',
		success: function (data) {
			var jsonResponse = JSON.parse(data);
			
			if (jsonResponse.status == "success") {
				jQuery.each(jsonResponse.data, function(key, entry) {
					// Create row and cells for each entry["entry"]
					var entry_row = document.createElement("tr");
					var delete_td = document.createElement("td");
					var entry_title_td = document.createElement("td");
					var entry_content_td = document.createElement("td");
					var entry_date_td = document.createElement("td");
					
					var entry_title_a = document.createElement("a");
					var entry_content_a = document.createElement("a");
					var entry_date_a = document.createElement("a");
					
					// Append the title to the title cell
					jQuery(entry_title_a).html(entry["entry"]._edata.title).attr({"href": "#", "data-toggle": "modal", "data-target": "#portfolio-modal", "data-type": entry["entry"].type + "-entry"}).addClass("edit-entry");
					jQuery(entry_title_td).append(entry_title_a).addClass("entry-title");
					
					// Append the date to the date cell
					jQuery(entry_date_a).html(format_date(entry["entry"].submitted_date, "yyyy-mm-dd")).attr({"href": "#", "data-toggle": "modal", "data-target": "#portfolio-modal", "data-type": entry["entry"].type + "-entry"}).addClass("edit-entry");
					jQuery(entry_date_td).append(entry_date_a).addClass("entry-date");

					// Check to see if the _edata object has a description or filename and put the data in the content cell
					if (entry["entry"]._edata.hasOwnProperty("description")) {
						
						var description = "";
						if (entry["entry"]._edata.description != null) {
							description = entry["entry"]._edata.description.replace(/(<([^>]+)>)/ig,"").substr(0, 80) + "...";
						}
						jQuery(entry_content_a).html(description).attr({"href": "#", "data-toggle": "modal", "data-target": "#portfolio-modal", "data-type": entry["entry"].type + "-entry"}).addClass("edit-entry");
						jQuery(entry_content_td).append(entry_content_a).addClass("entry-content");
					} 

					if (entry["entry"]._edata.hasOwnProperty("filename")) {
						jQuery(entry_content_a).html(entry["entry"]._edata.filename);
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
					jQuery(entry_row).append(delete_td).append(entry_title_td).append(entry_content_td).append(entry_date_td).attr({"data-id": entry["entry"].pentry_id, "data-artifact": entry["entry"].pfartifact_id});

					// Append entry["entry"] row to appropriate artifact
					jQuery("#artifact-" + entry["entry"].pfartifact_id).append(entry_row);
				});
			} else {
				// Create error row and cell
				var error_row = document.createElement("tr");
				var error_cell = document.createElement("td");
				jQuery(error_cell).append(jsonResponse.data).attr("colspan", "4");
				jQuery(error_row).append(error_cell).addClass("no-entries");
				jQuery("#artifact-" + pfartifact_id).append(error_row);
			}
		},
		error: function(data) {
			jQuery(".artifact-container").removeClass("loading");
			jQuery(".artifact .row-fluid, .artifact .btn-group").remove();
			display_error(["An error occurred while attempting to fetch the entries associated with this artifact. Please try again."], ".artifact", "append");
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
	//var pfartifact_id_input = document.createElement("input");
	//jQuery(pfartifact_id_input).attr({value: pfartifact_id, name: "pfartifact_id", id: "pfartifact_id", type: "hidden"});

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

	// Add appropriate form controls depending on the selected content type
	var method = jQuery("#method").val();
	switch (method) {
		
		case "file-entry" :
			var entry_control_group = document.createElement("div");
			jQuery(entry_control_group).addClass("control-group");
			var entry_controls = document.createElement("div");
			jQuery(entry_controls).addClass("controls");
			var entry_label = document.createElement("label");
			jQuery(entry_label).addClass("control-label");
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
			if (jQuery("#save-button").attr("data-action") == "edit") {
				jQuery(entry_label).html("File Name:").attr("for", "media-entry-upload");
				var entry_input = document.createElement("label");
				jQuery(entry_input).attr({id: "media-entry-upload", name: "file"}).addClass("control-label").css("text-align", "left");
			} else {
				jQuery(entry_label).html("Attach File:").attr("for", "media-entry-upload");
				var entry_input = document.createElement("input");
				jQuery(entry_input).attr({type: "file", id: "media-entry-upload", name: "file"});
			}
			
		break;
		case "reflection-entry" :
			var entry_control_group = document.createElement("div");
			jQuery(entry_control_group).addClass("control-group");
			var entry_controls = document.createElement("div");
			jQuery(entry_controls).addClass("controls");
			var entry_label = document.createElement("label");
			jQuery(entry_label).addClass("control-label");
			jQuery(entry_label).html("Reflection Body:").attr("for", "reflection-entry");
			var entry_input = document.createElement("textarea");
			jQuery(entry_input).attr({id: "entry-description", name: "description", "class": "reflection"});
		break;
		case "url-entry":
			var description_control_group = document.createElement("div");
			jQuery(description_control_group).addClass("control-group");
			var description_controls = document.createElement("div");
			jQuery(description_controls).addClass("controls");
			var description_input = document.createElement("input");
			jQuery(description_input).attr({name: "description", id: "entry-description", type: "text"});
			var description_label = document.createElement("label");
			jQuery(description_label).html("URL:").attr("for", "entry-description").addClass("control-label");
			jQuery(description_controls).append(description_input);
			jQuery(description_control_group).append(description_label).append(description_controls);
		break;
	}
	jQuery(entry_controls).append(entry_input);
	jQuery(entry_control_group).append(entry_label).append(entry_controls);
	jQuery("#portfolio-form").append(title_control_group).append(description_control_group).append(entry_control_group);
	if (jQuery("#entry-description").hasClass("reflection")) {
		jQuery("#entry-description").ckeditor();
	}
}

function appendArtifact (pfartifact_id, artifact_title) {
	var folder_artifact = document.createElement("div");
	var artifact_heading = document.createElement("h3");
	var artifact_heading_span = document.createElement("span");
	jQuery(artifact_heading_span).html(artifact_title).attr("data-artifact", pfartifact_id);
	jQuery(artifact_heading).append(artifact_heading_span);

	// Create the artifact meta data paragraph
	
	/*
	var artifact_meta = document.createElement("p");
	jQuery(artifact_meta).html((artifact.start_date > 0 ? "Created on: <strong>" + format_date(artifact.start_date) + "</strong>, " : "") + (artifact.finish_date > 0 ? "Due on: <strong>" + format_date(artifact.finish_date) + "</strong>" : "" )).addClass("muted");
	*/
   
	// Create artifact edit button
	var artifact_edit_a = document.createElement("a");
	jQuery(artifact_edit_a).html("<i class=\"icon-pencil\"></i>").addClass("btn btn-mini space-right edit-artifact").css("margin-top", "-3px").attr({"href": "#", "data-toggle": "modal", "data-target": "#portfolio-modal", "data-type": "artifact", "data-artifact": pfartifact_id});
	jQuery(artifact_heading).prepend(artifact_edit_a);

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
	jQuery(artifact_options).addClass("dropdown-menu").attr("data-artifact", pfartifact_id);

	// Create list items and links
	var artifact_option_reflection = document.createElement("li");
	var artifact_option_media = document.createElement("li");
	var artifact_option_link = document.createElement("li");
	
	var artifact_option_reflection_a = document.createElement("a");
	jQuery(artifact_option_reflection_a).html("Reflection");
	jQuery(artifact_option_reflection_a).attr({"href": "#", "data-toggle": "modal", "data-type": "reflection", "data-target": "#portfolio-modal"}).addClass("entry");
	var artifact_option_media_a = document.createElement("a");
	jQuery(artifact_option_media_a).html("Media");
	jQuery(artifact_option_media_a).attr({"href": "#", "data-toggle": "modal", "data-type": "file", "data-target": "#portfolio-modal"}).attr("data-target", "#portfolio-modal").addClass("entry");
	var artifact_option_link_a = document.createElement("a");
	jQuery(artifact_option_link_a).html("Link");
	jQuery(artifact_option_link_a).attr({"href": "#", "data-toggle": "modal", "data-type": "url", "data-target": "#portfolio-modal"}).attr("data-target", "#portfolio-modal").addClass("entry");

	// Append anchors to list items 
	jQuery(artifact_option_reflection).append(artifact_option_reflection_a);
	jQuery(artifact_option_media).append(artifact_option_media_a);
	jQuery(artifact_option_link).append(artifact_option_link_a);

	// Append list items to artifact options ul
	jQuery(artifact_options).append(artifact_option_media);
	jQuery(artifact_options).append(artifact_option_reflection);
	jQuery(artifact_options).append(artifact_option_link);

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

	var entries_delete_th =  document.createElement("th");
	jQuery(entries_delete_th).width("5%");
	jQuery(entries_thead_row).append(entries_delete_th);
	
	var entries_title_th =  document.createElement("th");
	jQuery(entries_title_th).width("30%");
	jQuery(entries_title_th).html("Title");
	jQuery(entries_thead_row).append(entries_title_th);
	
	var entries_content_th =  document.createElement("th");
	jQuery(entries_content_th).html("Content");
	jQuery(entries_thead_row).append(entries_content_th);
	
	var entries_date_th =  document.createElement("th");
	jQuery(entries_date_th).width("25%");
	jQuery(entries_date_th).html("Submitted Date");
	jQuery(entries_thead_row).append(entries_date_th);

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
			appendArtifact(jsonResponse.pentry_id, jsonResponse.title);
			var entry_row = document.createElement("tr");
			var entry_cell = document.createElement("td");
			jQuery(entry_cell).append("No entries attached to this artifact.").attr("colspan", "4");
			jQuery(entry_row).append(entry_cell).addClass("no-entries");
			jQuery("#artifact-" + jsonResponse.pentry_id).append(entry_row);	
		break;
		case "artifact-edit" :
			jQuery("span[data-artifact="+ jsonResponse.pentry_id + "]").html(jsonResponse.title);
		break;
		case "reflection" :
			if (jQuery("[data-id="+ jsonResponse.pentry_id + "]").length) {
				jQuery("[data-id="+ jsonResponse.pentry_id + "] .entry-title").children("a").html(jQuery("#media-entry-title").val());
				jQuery("[data-id="+ jsonResponse.pentry_id + "] .entry-content").children("a").html(jQuery("#entry-description").val());
			} else {
				var entry_row = document.createElement("tr");			
				var entry_delete_cell = document.createElement("td");
				var entry_delete_button = document.createElement("button");
				var entry_date_a =  document.createElement("a");
				var entry_content_a =  document.createElement("a");
				jQuery(entry_delete_button).addClass("btn btn-mini btn-danger").html("<i class=\"icon-trash icon-white\"></i>");
				jQuery(entry_delete_cell).append(entry_delete_button);
				
				var entry_title_cell = document.createElement("td");
				var entry_title_a = document.createElement("a");
				jQuery(entry_title_a).html(jsonResponse.edata.title).attr({"href": "#", "data-toggle": "modal", "data-target": "#portfolio-modal", "data-type": type + "-entry"}).addClass("edit-entry");
				jQuery(entry_title_cell).append(entry_title_a).addClass("entry-title");

				var entry_date_cell = document.createElement("td");
				var date = new Date(jsonResponse.submitted_date * 1000);
				jQuery(entry_date_a).html(date.getFullYear() + "-" + (date.getMonth() <= 9 ? "0" : "") + (date.getMonth() + 1) + "-" +  (date.getDate() <= 9 ? "0" : "") + date.getDate()).attr({"href": "#", "data-toggle": "modal", "data-target": "#portfolio-modal", "data-type": type + "-entry"}).addClass("edit-entry");
				jQuery(entry_date_cell).append(entry_date_a).addClass("entry-date");

				var entry_content_cell = document.createElement("td");
				if (jsonResponse.edata.description.length > 80) {
					content = jsonResponse.edata.description.replace(/(<([^>]+)>)/ig,"").substr(0, 80) + "...";
				} else {
					content = jsonResponse.edata.description;
				}
				
				jQuery(entry_content_a).html(content).attr({"href": "#", "data-toggle": "modal", "data-target": "#portfolio-modal", "data-type": type + "-entry"}).addClass("edit-entry");
				jQuery(entry_content_cell).append(entry_content_a).addClass("entry-content");
				jQuery(entry_row).append(entry_delete_cell).append(entry_title_cell).append(entry_content_cell).append(entry_date_cell).attr("data-id", jsonResponse.pentry_id);
				jQuery("#artifact-" + pfartifact_id).append(entry_row);
			}
			
			if (jQuery("#artifact-" + pfartifact_id + " .no-entries").length) {
				jQuery(".no-entries").remove();
			}
			
		break;
		case "file" :
			if (jQuery("[data-id="+ jsonResponse.pentry_id + "]").length) {
				jQuery("[data-id="+ jsonResponse.pentry_id + "] .entry-title").children("a").html(jQuery("#media-entry-title").val());
			}
		break;
	}
}

function populateEntryForm(pfartifact_id, pentry_id) {
	jQuery.ajax({
		url: ENTRADA_URL + "/api/eportfolio.api.php",
		data: "method=get-entry&pentry_id=" + pentry_id,
		type: 'GET',
		async: false,
		success: function (data) {
			var jsonResponse = JSON.parse(data);
			if (jsonResponse.status === "success") {
				entryForm(pfartifact_id);
				jQuery("#media-entry-title").val(jsonResponse.data._edata.title);
				jQuery("#entry-description").val(jsonResponse.data._edata.description);
				switch (jsonResponse.data.type) {
					case "reflection" :
						jQuery(".modal-header h3").html("Edit Entry");
					break;
					case "file" :
						jQuery(".modal-header h3").html("Edit Entry");
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
					case "url" :
						jQuery(".modal-header h3").html("Edit Entry");
						var link_control_group = document.createElement("div");
						jQuery(link_control_group).addClass("control-group");
						var link_controls = document.createElement("div");
						jQuery(link_controls).addClass("controls");
						var link_label = document.createElement("label");
						jQuery(link_label).css("width", "150px").addClass("control-label");
						var link_a = document.createElement("a");
						jQuery(link_a).html("<i class=\"icon-bookmark\"></i>" + jsonResponse.data._edata.description).attr("href", jsonResponse.data._edata.description);
						jQuery(link_controls).append(link_a);
						jQuery(link_control_group).append(link_label).append(link_controls);
						jQuery("#portfolio-form").append(link_control_group);
					break;
				}
			} else {
				display_error(jsonResponse.data, "#modal-msg", "append");
			}
		},
		error: function (data) {
			jQuery(".artifact-container").removeClass("loading");
			display_error(["An error occurred while attempting to fetch the entry. Please try again."], "#modal-msg", "append");
		}
	});
}

function populateArtifactForm (pfartifact_id) {
	artifactForm();
	jQuery.ajax({
		url: ENTRADA_URL + "/api/eportfolio.api.php",
		data: "method=get-folder-artifact&pfartifact_id=" + pfartifact_id,
		type: 'GET',
		async: false,
		success: function (data) {
			var jsonResponse = JSON.parse(data);
			if (jsonResponse.status == "success") {
				jQuery("#artifact-title").val(jsonResponse.data.title);
				jQuery("#artifact-description").val(jsonResponse.data.description);
			} else {
				display_error(jsonResponse.data, "#modal-msg", "append");
			}
		},
		error: function (data) {
			jQuery(".artifact-container").removeClass("loading");
			display_error(["An error occurred while attempting to fetch the artifact. Please try again."], "#modal-msg", "append");
		}
	});	
}