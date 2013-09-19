<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Entrada is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Entrada.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ePortfolio public index
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @author Developer: Josh Dillon <josh.dillon@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
 *
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!defined("PARENT_INCLUDED")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("eportfolio", "read")) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/eportfolio.js\"></script>";
	$HEAD[] = "<script type=\"text/javascript\">var ENTRADA_URL = '".ENTRADA_URL."';</script>";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/ckeditor/adapters/jquery.js\"></script>\n";
	load_rte("minimal");
	?>
	<h1>Entrada ePortfolio</h1>
	<?php
	$eportfolio = Models_Eportfolio::fetchRowByGroupID($ENTRADA_USER->getCohort());
	$folders = $eportfolio->getFolders();
	?>
	
	<h2><?php echo $eportfolio->getPortfolioName(); ?></h2>
	<script type="text/javascript">
		jQuery(document).ready(function ($) {
			var pfolder_id = $("#folder-list").children(":first").children("a").data("id");
			getFolder(pfolder_id);
			$(".folder-item").on("click", function (e) {
				e.preventDefault();
				var pfolder_id = $(this).data("id");
				getFolder(pfolder_id);
			});
			
			function getFolder (pfolder_id) {
				$.ajax({
					url: "<?php echo ENTRADA_URL; ?>/api/eportfolio.api.php",
					data: "method=get-folder&pfolder_id=" + pfolder_id,
					type: 'GET',
					success:function (data) {
						var jsonResponse = JSON.parse(data);
						if (jsonResponse.status == "success") {
							$("#folder-title").html(jsonResponse.data.title);
							getFolderArtifacts(pfolder_id);
						}
					}	
				});
			}
			
			function getFolderArtifacts (pfolder_id) {
				var proxy_id = "<?php echo $ENTRADA_USER->getProxyId(); ?>";
				$.ajax({
					url: "<?php echo ENTRADA_URL; ?>/api/eportfolio.api.php",
					data: "method=get-folder-artifacts&pfolder_id=" + pfolder_id + "&proxy_id=" + proxy_id,
					type: 'GET',
					success:function (data) {
						var jsonResponse = JSON.parse(data);
						$(".artifact-container").empty();
						if (jsonResponse.status == "success") {
							if ($("#display-notice-box-modal").length) {
								$("#msgs").empty();
							}
							
							$.each(jsonResponse.data, function (key, artifact) {
								var folder_artifact = document.createElement("div");
								var pfartifact_id = artifact.pfartifact_id;
								var artifact_heading = document.createElement("h3");
								$(artifact_heading).html(artifact.title);
								
								// Create the button group for artifact Content 
								var artifact_content = document.createElement("div");
								$(artifact_content).addClass("btn-group pull-right space-below");
								var artifact_options_button = document.createElement("button");
								$(artifact_options_button).addClass("btn btn-primary dropdown-toggle").attr("data-toggle", "dropdown").html("Add Content ");
								var artifact_options_span = document.createElement("span");
								$(artifact_options_span).addClass("caret");
								
								// Append the the artifact content button to the button group
								$(artifact_options_button).append(artifact_options_span);
								$(artifact_content).append(artifact_options_button);
								
								// Create the options list for the artifact content button group
								var artifact_options = document.createElement("ul");
								$(artifact_options).addClass("dropdown-menu");
								
								// Create list items and links
								var artifact_option_reflection = document.createElement("li");
								var artifact_option_media = document.createElement("li");
								var artifact_option_reflection_a = document.createElement("a");
								$(artifact_option_reflection_a).html("Reflection");
								$(artifact_option_reflection_a).attr("data-toggle", "modal").attr("data-target", "#portfolio-modal").addClass("reflection-content");
								var artifact_option_media_a = document.createElement("a");
								$(artifact_option_media_a).html("Media");
								$(artifact_option_media_a).attr("data-toggle", "modal").attr("data-target", "#portfolio-modal").addClass("media-content");
								
								// Attach click event to reflection and media links to update the contents of the portfolio-modal
								$(artifact_option_reflection_a).on("click", function (e) {
									e.preventDefault();
									$("#method").attr("value", "reflection-entry");
									$(".modal-header h3").html("Add Reflection");
									$("#save-button").html("Save Reflection");
									entryForm();
								});
								
								$(artifact_option_media_a).on("click", function (e) {
									e.preventDefault();
									$("#method").attr("value", "media-entry");
									$(".modal-header h3").html("Add Media");
									$("#save-button").html("Save Media");
									entryForm();
								});
								
								// Append anchors to list items 
								$(artifact_option_reflection).append(artifact_option_reflection_a);
								$(artifact_option_media).append(artifact_option_media_a);
								
								// Append list items to artifact options ul
								$(artifact_options).append(artifact_option_media);
								$(artifact_options).append(artifact_option_reflection);
								
								// Append the artifact options ul to the button group
								$(artifact_content).append(artifact_options);
								
								var artifact_row = document.createElement("div");
								$(artifact_row).addClass("row-fluid");
								
								var artifact_entries = document.createElement("div");
								$(artifact_entries).addClass("span12");
								
								var entries_table = document.createElement("table");
								$(entries_table).addClass("table table-striped table-bordered");
								$(entries_table).attr("id", "artifact-" + artifact.pfartifact_id);
								
								var entries_thead = document.createElement("thead");
								$(entries_table).append(entries_thead);
								
								var entries_thead_row = document.createElement("tr");
								$(entries_thead).append(entries_thead_row);
								
								var entries_title_th =  document.createElement("th");
								$(entries_title_th).width("5%");
								$(entries_thead_row).append(entries_title_th);
								
								var entries_date_th =  document.createElement("th");
								$(entries_date_th).width("25%");
								$(entries_date_th).html("Submitted Date");
								$(entries_thead_row).append(entries_date_th);
								
								var entries_content_th =  document.createElement("th");
								$(entries_content_th).html("Content");
								$(entries_thead_row).append(entries_content_th);
								
								$(artifact_entries).append(entries_table);
								$(artifact_row).append(artifact_entries);
								$(folder_artifact).addClass("artifact").append(artifact_heading).append(artifact_content).append(artifact_row);
								$(".artifact-container").append(folder_artifact);
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
			var proxy_id = "<?php echo $ENTRADA_USER->getProxyId(); ?>";
				$.ajax({
					url: "<?php echo ENTRADA_URL; ?>/api/eportfolio.api.php",
					data: "method=get-artifact-entries&pfartifact_id=" + pfartifact_id + "&proxy_id=" + proxy_id,
					type: 'GET',
					success:function (data) {
						console.log(data);
						var jsonResponse = JSON.parse(data);
						if (jsonResponse.status == "success") {
							$.each(jsonResponse.data, function(key, entry) {
								// Create row and cells for each entry
								var entry_row = document.createElement("tr");
								var delete_td = document.createElement("td");
								var entry_date_td = document.createElement("td");
								var entry_content_td = document.createElement("td");
								
								// Append the date to the date cell
								$(entry_date_td).html(format_date(entry.submitted_date, "yyyy-mm-dd"));
								
								// Check to see if the _edata object has a description or filename and put the data in the content cell
								if (entry._edata.hasOwnProperty("description")) {
									var description = entry._edata.description.replace(/(<([^>]+)>)/ig,"").substr(0, 80) + "...";
									$(entry_content_td).html(description);
								} 
								
								if (entry._edata.hasOwnProperty("filename")) {
									$(entry_content_td).html(entry._edata.filename);
								}
								
								// Create delete button and icon
								var delete_button = document.createElement("button");
								$(delete_button).addClass("btn btn-mini btn-danger");
								
								var delete_icon = document.createElement("i");
								$(delete_icon).addClass("icon-trash icon-white");
								
								// Append the icon to the button and then append the button to the delete cell
								$(delete_button).append(delete_icon);								
								$(delete_td).append(delete_button);

								// Append cells to the enrty row
								$(entry_row).append(delete_td).append(entry_date_td).append(entry_content_td);
								
								// Append entry row to appropriate artifact
								$("#artifact-" + entry.pfartifact_id).append(entry_row);
							});
						}
					}	
				});
			}
			$("#create-artifact").on("click", function () {
				$(".modal-header h3").html("Create Artifact");
				$("#save-button").html("Save Artifact");
				artifactForm();
			});
			
			function artifactForm () {
				if ($("#portfolio-form .control-group").length) {
					$(".control-group").remove();
				}
				
				// Create the divs that will hold the form controls for the create artifact form
				var title_control_group = document.createElement("div");
				$(title_control_group).addClass("control-group");
				var title_controls = document.createElement("div");
				$(title_controls).addClass("controls");
				var description_control_group = document.createElement("div");
				$(description_control_group).addClass("control-group");
				var description_controls = document.createElement("div");
				$(description_controls).addClass("controls");
				
				// Create the form controls
				var title_input = document.createElement("input");
				$(title_input).attr({type: "text", name: "title", id: "artifact-title"}).addClass("input-large");
				
				var description_textarea = document.createElement("textarea");
				$(description_textarea).attr({name: "description", id: "artifact-description"}).addClass("input-large");
				
				// Create the labels for the create artifact form controls
				var title_label = document.createElement("label");
				$(title_label).html("Title:").attr("for", "artifact-title").addClass("control-label");
				var description_label = document.createElement("label");
				$(description_label).html("Description:").attr("for", "artifact-description").addClass("control-label");
				
				// Put it all together
				$(title_controls).append(title_input);
				$(title_control_group).append(title_label).append(title_controls);
				$(description_controls).append(description_textarea);
				$(description_control_group).append(description_label).append(description_controls);
				$("#portfolio-form").append(title_control_group).append(description_control_group);
			}
			
			function entryForm () {
				if ($("#portfolio-form .control-group").length) {
					$(".control-group").remove();
				}
				
				// Create the divs that will hold the form controls for the create artifact form
				var title_control_group = document.createElement("div");
				$(title_control_group).addClass("control-group");
				var title_controls = document.createElement("div");
				$(title_controls).addClass("controls");
				
				// Create the form controls
				var title_input = document.createElement("input");
				$(title_input).attr({type: "text", name: "title", id: "media-entry-title"}).addClass("input-large");
				

				// Create the labels for the create artifact form controls
				var title_label = document.createElement("label");
				$(title_label).html("Title:").attr("for", "media-entry-title").addClass("control-label");
				
				
				// Put it all together
				$(title_controls).append(title_input);
				$(title_control_group).append(title_label).append(title_controls);
				
				var entry_control_group = document.createElement("div");
				$(entry_control_group).addClass("control-group");
				var entry_controls = document.createElement("div");
				$(entry_controls).addClass("controls");
				var entry_label = document.createElement("label");
				$(entry_label).addClass("control-label");
				
				// Add appropriate form controls depending on the selected content type
				var method = $("#method").val();
				switch (method) {
					case "media-entry" :
						var description_control_group = document.createElement("div");
						$(description_control_group).addClass("control-group");
						var description_controls = document.createElement("div");
						$(description_controls).addClass("controls");
						var description_textarea = document.createElement("textarea");
						$(description_textarea).attr({name: "description", id: "media-entry-description"}).addClass("input-large");
						var description_label = document.createElement("label");
						$(description_label).html("Description:").attr("for", "media-entry-description").addClass("control-label");
						$(description_controls).append(description_textarea);
						$(description_control_group).append(description_label).append(description_controls);
						$(entry_label).html("Attach File:").attr("for", "media-entry-upload");
						var entry_input = document.createElement("input");
						$(entry_input).attr({type: "file", id: "media-entry-upload"});
						
					break;
					case "reflection-entry" :
						$(entry_label).html("Reflection Body:").attr("for", "reflection-entry");
						var entry_input = document.createElement("textarea");
						$(entry_input).attr({id: "reflection-entry"});
					break;
				}
				$(entry_controls).append(entry_input);
				$(entry_control_group).append(entry_label).append(entry_controls);
				$("#portfolio-form").append(title_control_group).append(description_control_group).append(entry_control_group);
				$("#reflection-entry").ckeditor();
			}
		});
	</script>
	<div class="btn-group">
		<a class="btn btn-primary">Folders</a>
		<a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>
		<ul class="dropdown-menu" id="folder-list">
		<?php 
		foreach ($folders as $folder) { ?>
			<li>
				<a href="#" data-id="<?php echo $folder->getID(); ?>" class="folder-item"><?php echo $folder->getTitle(); ?></a>
			</li>
		<?php
		}
		?>
		</ul>
	</div>
	<div class="folder-container">
		<h1 id="folder-title"></h1>
		<div class="row">
			<a href="#" class="btn btn-primary pull-right space-below" data-toggle="modal" data-target="#portfolio-modal" id="create-artifact">Create Artifact</a>
		</div>
		<div id="msgs"></div>
		<div class="artifact-container"></div>
	</div>
	<div class="modal hide fade" id="portfolio-modal" style="width:700px;">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3></h3>
		</div>
		<div class="modal-body">
			<div id="modal-msg"></div>
			<form action="" method="POST" class="form-horizontal" id="portfolio-form">
				<input type="hidden" value="create-artifact" class="method" name="method" id="method" />
			</form>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn pull-left" data-dismiss="modal">Cancel</a>
			<a href="#" class="btn btn-primary" id="save-button">Save changes</a>
		</div>
	</div>
	<?php
	if ($folders) {
		echo "<ul>";
		foreach ($folders as $folder) {
			echo "<li>";
			echo "<h3>".$folder->getTitle()."<a href=\"#\" data-pfolder-id=\"".$folder->getID()."\" class=\"add-artifact\"><i class=\"icon-plus-sign\"></i></a></h3>";
			$artifacts = $folder->getArtifacts($ENTRADA_USER->getID());
			if ($artifacts) {
				echo "<ul>";
				foreach ($artifacts as $artifact) {
					echo "<li>";
					echo "<h4>".$artifact->getTitle()."<a href=\"#\" data-pfartifact-id=\"".$artifact->getID()."\" class=\"add-entry\"><i class=\"icon-plus\"></i></a></h4>";
					$entries = $artifact->getEntries($ENTRADA_USER->getID());
					if ($entries) {
						echo "<ul>";
						foreach ($entries as $entry) {
							echo "<li>";
							$edata = $entry->getEdataDecoded(); 
							if (isset($edata["filename"]) && !empty($edata["filename"])) {
								echo "<a href=\"#\">".$edata["filename"]."</a>";
							} else if (isset($edata["description"]) && !empty($edata["description"])) {
								echo $edata["description"];
							}
							echo "</li>";
						}
						echo "</ul>";
					}
					echo "</li>";
				}
				echo "</ul>";
			}
			echo "</li>";
		}
		echo "</ul>";
	}
}

