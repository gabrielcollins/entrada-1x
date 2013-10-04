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
	$HEAD[] = "<script type=\"text/javascript\">var ENTRADA_URL = '".ENTRADA_URL."'; var PROXY_ID = '".$ENTRADA_USER->getProxyId()."'; var FLAGGED = false;</script>";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/eportfolio.js\"></script>";
	load_rte("minimal");
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/ckeditor/adapters/jquery.js\"></script>\n";
	?>
	<h1>Entrada ePortfolio</h1>
	<?php
	$eportfolios = Models_Eportfolio::fetchAll($ENTRADA_USER->getActiveOrganisation());
	?>
	<script type="text/javascript">
		function getPortfolio(portfolio_id) {
			jQuery.ajax({
				url: ENTRADA_URL + "/api/eportfolio.api.php",
				data: "method=get-portfolio-members&portfolio_id=" + portfolio_id + (FLAGGED === true ? "&flagged=true" : ""),
				type: 'GET',
				success:function (data) {
					var jsonResponse = JSON.parse(data);
					if (jsonResponse.status == "success") {
						jQuery("#user-list").html("");
						var user_list = document.createElement("ul");
						var back_row = document.createElement("li");
						var back_btn = document.createElement("a");
						jQuery(back_row).append(back_btn);
						jQuery(user_list).append(back_row);
						jQuery(back_btn).html("<i class=\"icon-chevron-left\"></i> Back")
						for (var i=0; i < jsonResponse.data.length; i++) {
							var user_row = document.createElement("li");
							var user_link = document.createElement("a");
							jQuery(user_link).addClass("portfolio-user");
							jQuery(user_link).attr("data-proxy-id", jsonResponse.data[i].proxy_id).attr("data-portfolio-id", portfolio_id).attr("href", "#").html(jsonResponse.data[i].lastname + ", " + jsonResponse.data[i].firstname);
							jQuery(user_row).append(user_link);
							jQuery(user_list).append(user_row);
						}
						jQuery("#user-list").append(user_list);
					}
				}
			});
		}
		
		function getFolders(portfolio_id) {
			jQuery.ajax({
				url: ENTRADA_URL + "/api/eportfolio.api.php",
				data: "method=get-folders&portfolio_id=" + portfolio_id + (FLAGGED === true ? "&flagged=true&proxy_id="+PROXY_ID : ""),
				type: 'GET',
				success:function (data) {
					var jsonResponse = JSON.parse(data);
					if (jsonResponse.status == "success") {
						var folder_list = document.createElement("ul");
						jQuery.each(jsonResponse.data, function(i, v) {
							var folder_row = document.createElement("li");
							var folder_link = document.createElement("a");
							jQuery(folder_link).addClass("portfolio-folder");
							jQuery(folder_link).attr("data-pfolder-id", v.pfolder_id).attr("href", "#").html(v.title);
							jQuery(folder_row).append(folder_link);
							jQuery(folder_list).append(folder_row);
						});
						jQuery("#user-portfolio").append(folder_list);
					}
				}
			});
		}
		
		function adminArtifactForm(btn) {
			artifactForm();
			
			var reviewers_control_group = document.createElement("div");
			jQuery(reviewers_control_group).addClass("control-group");
			var reviewers_label = document.createElement("label");
			jQuery(reviewers_label).addClass("control-label").html("Reviewers:").attr("for", "reviewers");
			jQuery(reviewers_control_group).append(reviewers_label);
			var reviewers_controls = document.createElement("div");
			jQuery(reviewers_controls).addClass("controls");
			var reviewers_input = document.createElement("input");
			jQuery(reviewers_input).attr("type", "text").attr("name", "reviewers[]").attr("id", "reviewers");
			jQuery(reviewers_controls).append(reviewers_input);
			jQuery(reviewers_control_group).append(reviewers_controls);

			var start_date_control_group = document.createElement("div");
			jQuery(start_date_control_group).addClass("control-group");
			var start_date_label = document.createElement("label");
			jQuery(start_date_label).addClass("control-label").html("Start:").attr("for", "start_date");
			jQuery(start_date_control_group).append(start_date_label);
			var start_date_controls = document.createElement("div");
			jQuery(start_date_controls).addClass("controls");
			var start_date_input_container = document.createElement("div");
			jQuery(start_date_input_container).addClass("input-prepend").html("<span class=\"add-on\"><i class=\"icon-calendar\"></i></span>");
			var start_date_input = document.createElement("input");
			jQuery(start_date_input).attr("type", "text").attr("name", "start_date").attr("id", "start_date").addClass("input-small");
			jQuery(start_date_input_container).append(start_date_input);
			jQuery(start_date_controls).append(start_date_input_container);
			jQuery(start_date_control_group).append(start_date_controls);

			var finish_date_control_group = document.createElement("div");
			jQuery(finish_date_control_group).addClass("control-group");
			var finish_date_label = document.createElement("label");
			jQuery(finish_date_label).addClass("control-label").html("Finish:").attr("for", "finish_date");
			jQuery(finish_date_control_group).append(finish_date_label);
			var finish_date_controls = document.createElement("div");
			jQuery(finish_date_controls).addClass("controls");
			var finish_date_input_container = document.createElement("div");
			jQuery(finish_date_input_container).addClass("input-prepend").html("<span class=\"add-on\"><i class=\"icon-calendar\"></i></span>");
			var finish_date_input = document.createElement("input");
			jQuery(finish_date_input).attr("type", "text").attr("name", "finish_date").attr("id", "finish_date").addClass("input-small");
			jQuery(finish_date_input_container).append(finish_date_input);
			jQuery(finish_date_controls).append(finish_date_input_container);
			jQuery(finish_date_control_group).append(finish_date_controls);

			var enable_commenting_control_group = document.createElement("div");
			jQuery(enable_commenting_control_group).addClass("control-group");
			var enable_commenting_label = document.createElement("label");
			jQuery(enable_commenting_label).addClass("control-label").html("Allow commenting:").attr("for", "allow_commenting");
			jQuery(enable_commenting_control_group).append(enable_commenting_label);
			var enable_commenting_controls = document.createElement("div");
			jQuery(enable_commenting_controls).addClass("controls");
			var enable_commenting_input = document.createElement("input");
			jQuery(enable_commenting_input).attr("type", "checkbox").attr("name", "allow_commenting");
			jQuery(enable_commenting_controls).append(enable_commenting_input);
			jQuery(enable_commenting_control_group).append(enable_commenting_controls);

			var pfolder_id_input = document.createElement("input");
			jQuery(pfolder_id_input).attr({"type" : "hidden", "name" : "pfolder_id", "value" : btn.data("pfolder-id")});

			jQuery("#portfolio-form").append(pfolder_id_input).append("<input type=\"hidden\" name=\"method\" value=\"create-artifact\" />").append(reviewers_control_group).append(start_date_control_group).append(finish_date_control_group).append(enable_commenting_control_group).attr("action", ENTRADA_URL + "/api/eportfolio.api.php");
			jQuery("#start_date").datepicker({ dateFormat: "yy-mm-dd" });
			jQuery("#finish_date").datepicker({ dateFormat: "yy-mm-dd" });
			jQuery("#artifact-description").ckeditor()
		}
		
		jQuery(function($) {
			$("#portfolio-list, #breadcrumb").on("click", ".portfolio-item", function (e) {
				portfolio_id = $(this).data("id");
				getPortfolio(portfolio_id);
				location.hash = $(this).attr("data-id");
				
				$("#breadcrumb").html("");
				$("#user-portfolio").html("");
				var span = document.createElement("span");
				var breadcrumb_link = $(this).clone();
				$(span).append(breadcrumb_link);
				$("#breadcrumb").append(span);
				
				jQuery("#user-portfolio").html("<h1>"+$(breadcrumb_link).html()+"</h1>");
				display_notice(["Select a learner from the menu on the left to review their portfolio."], $("#user-portfolio"), "append");
				
				e.preventDefault();
			});
			$("#portfolio-container, #breadcrumb").on("click", ".portfolio-user", function(e) {
				$(".portfolio-user").removeClass("active");
				$(this).addClass("active");
				PROXY_ID = $(this).data("proxy-id");
				portfolio_id = $(this).data("portfolio-id");
				
				$("#breadcrumb .portfolio-user").parent().remove();
				$("#breadcrumb .portfolio-folder").parent().remove();
				var span = document.createElement("span");
				var breadcrumb_link = $(this).clone();
				$(span).append(" / ").append(breadcrumb_link);
				$("#breadcrumb").append(span);
				
				jQuery("#user-portfolio").html("<h1>"+$(breadcrumb_link).html()+"</h1>");
				
				getFolders(portfolio_id);
				
				e.preventDefault();
			});
			$("#portfolio-container, #breadcrumb").on("click", ".portfolio-folder", function(e) {
				
				$("#user-portfolio").html("");
				
				$("#breadcrumb .portfolio-folder").parent().remove();
				var span = document.createElement("span");
				var breadcrumb_link = $(this).clone();
				$(span).append(" / ").append(breadcrumb_link);
				$("#breadcrumb").append(span);
				$("#user-portfolio").append("<h1>" + $(breadcrumb_link).html() + "</h1>");
				
				var pfolder_id = $(this).data("pfolder-id");
				var proxy_id = PROXY_ID;
				$.ajax({
					url: ENTRADA_URL + "/api/eportfolio.api.php",
					data: "method=get-folder-artifacts&pfolder_id=" + pfolder_id + "&proxy_id=" + proxy_id,
					type: 'GET',
					success:function (data) {
						var jsonResponse = JSON.parse(data);
						var artifact_list = document.createElement("ul");
						
						if (typeof jsonResponse.data != "string") {
							$.each(jsonResponse.data, function(i, v) {
								var artifact_row = document.createElement("li");
								var artifact_title = document.createElement("h2");
								var pfartifact_id = v.pfartifact_id;
								$(artifact_title).html(v.title);
								var entries = document.createElement("ul");
								$.ajax({
									url : ENTRADA_URL + "/api/eportfolio.api.php",
									data : "method=get-artifact-entries&pfartifact_id=" + pfartifact_id + "&proxy_id=" + proxy_id,
									type : 'GET',
									success : function (data) {
										var entryJsonResponse = JSON.parse(data);
										if (typeof entryJsonResponse.data != "string") {
											$.each(entryJsonResponse.data, function(i, v) {
												var entry_row = document.createElement("li");
												$(entry_row).addClass("well");
												if (typeof v.entry._edata != 'undefined') {
													if (typeof v.entry._edata.title != 'undefined' && v.entry._edata.description.title > 0) {
														$(entry_row).append("<h3>" + v.entry._edata.title + "</h3>");
													}
													if (typeof v.entry._edata.filename != 'undefined' && v.entry._edata.filename.length > 0) {
														var entry_link = document.createElement("a");
														$(entry_link).attr("href", "#").html(v.entry._edata.filename);
														$(entry_row).append(entry_link);
													}
													if (typeof v.entry._edata.description != 'undefined' && v.entry._edata.description.length > 0) {
														$(entry_row).append("<div>" + v.entry._edata.description + "</div>");
													}
													
													if (typeof v.comments != 'undefined') {
														var comment_container = document.createElement("div");
														$(comment_container).addClass("comments well space-above").html("<strong>Comments:</strong><hr />").attr("id", "comments-"+v.entry.pentry_id);
														$.each(v.comments, function(c_i, comment) {
															$(comment_container).append("<div class=\"comment\">&ldquo;"+comment.comment+"&rdquo;<br /><span class=\"muted content-small\">"+comment.commentor+" - "+comment.submitted_date + "</span> - <i class=\"icon-trash comment-delete\" style=\"cursor:pointer;\" data-pecomment-id=\""+comment.pecomment_id+"\"></i><hr /></div>");
														});
														$(entry_row).append(comment_container);
													}
													
													var entry_controls = document.createElement("div");
													$(entry_controls).addClass("row-fluid space-above");
													
													var flag_btn = document.createElement("button");
													$(flag_btn).addClass("btn btn-danger btn-mini pull-right add-flag space-right" + (v.entry.flag == 1 ? " flagged" : "")).attr("data-pentry-id", v.entry.pentry_id).html("<i class=\"icon-flag icon-white\"></i> " + (v.entry.flag == 1 ? "Flagged" : "Flag"))
													
													var review_btn = document.createElement("button");
													$(review_btn).addClass("btn btn-mini pull-right add-review space-right" + (v.entry.reviewed_date > 0 ? " reviewed" : "")).attr("data-pentry-id", v.entry.pentry_id).html("<i class=\"icon-check\"></i> " + (v.entry.reviewed_date > 0 ? "Reviewed" : "Review"))
													
													var comment_btn = document.createElement("button");
													$(comment_btn).addClass("btn btn-success btn-mini pull-right add-comment").attr("data-pentry-id", v.entry.pentry_id).html("<i class=\"icon-plus-sign icon-white\"></i> Add Comment")
													
													$(entry_controls).append(comment_btn);
													$(entry_controls).append(flag_btn);
													$(entry_controls).append(review_btn);
													
													$(entry_row).append(entry_controls);
													$(entries).append(entry_row);
												}
											});
										} else {
											$(entries).append("<div class=\"alert alert-block alert-notice\"><ul><li>" + entryJsonResponse.data + "</li></ul></div>");
										}
									}
								});

								$(artifact_row).append(artifact_title).append(entries);
								
								$(artifact_list).append(artifact_row);
							});
							$("#user-portfolio").append(artifact_list);
						} else {
							display_notice(["The folder you are attempting to view does not have any associated artifacts. Use the manage tab to add artifacts to the folder."], $("#user-portfolio"), "append");
						}
					}
				});
				e.preventDefault();
			});
			$("#flag-toggle button").on("click", function(e) {
				$("#flag-toggle button").removeClass("active");
				$(this).addClass("active");
				if ($(this).hasClass("flagged")) {
					FLAGGED = true;
				} else {
					FLAGGED = false;
				}
			
				e.preventDefault();
			})
			$("#user-portfolio").on("click", ".add-comment", function(e) {

				$("#entry-modal .modal-header h3").html("Add Comment");
				$("#entry-modal .modal-footer .btn-primary").html("Save Comment");

				var comment_row = document.createElement("div");
				$(comment_row).addClass("control-group");
				
				var comment_label = document.createElement("label");
				$(comment_label).addClass("control-label form-required").attr("for", "entry-comment").html("Comment");
				
				$(comment_row).append(comment_label);
				
				var comment_box_container = document.createElement("div");
				$(comment_box_container).addClass("controls");
				var comment_box = document.createElement("textarea");
				$(comment_box).attr("id", "entry-comment").attr("name", "entry-comment");
				
				$(comment_box_container).append(comment_box);
				$(comment_row).append(comment_box_container);
				
				$("#entry-modal .modal-body #modal-form").append(comment_row).append("<input type=\"hidden\" name=\"pentry_id\" value=\""+$(this).data("pentry-id")+"\" />");
				$("#entry-modal").modal("show");
				
				e.preventDefault();
			});
			$("#modal-form").on("submit", function(e) {
				var form = $(this);
				
				$.ajax({
				url : ENTRADA_URL + "/api/eportfolio.api.php",
					type : "POST",
					data : "method=add-pentry-comment&" + form.serialize(),
					success: function(data) {
						var jsonResponse = JSON.parse(data);
						if (jsonResponse.status == "success") {
							var comment = "&ldquo;"+jsonResponse.data.comment+"&rdquo;<br /><span class=\"muted content-small\">"+jsonResponse.data.commentor+" - "+jsonResponse.data.submitted_date+"</span><hr />";
							$("#comments-"+jsonResponse.data.pentry_id).append(comment);
						}
					}
				});

				e.preventDefault();
			});
			$("#entry-modal .modal-footer .btn-primary").on("click", function(e) {
				$("#modal-form").submit();
				e.preventDefault();
			});
			$("#user-portfolio").on("click", ".add-flag", function(e) {
				var btn = $(this);
				var action = "flag";
				if (btn.hasClass("flagged")) {
					action = "unflag";
				}
				$.ajax({
				url : ENTRADA_URL + "/api/eportfolio.api.php",
					type : "POST",
					data : "method=pentry-flag&action="+action+"&pentry_id=" + btn.data("pentry-id"),
					success: function(data) {
						var jsonResponse = JSON.parse(data);
						if (jsonResponse.data.flag == 1) {
							btn.addClass("flagged").html("<i class=\"icon-flag icon-white\"></i> Flagged");
						} else {
							btn.removeClass("flagged").html("<i class=\"icon-flag icon-white\"></i> Flag");
						}
					}
				});
				e.preventDefault();
			});
			$("#user-portfolio").on("click", ".add-review", function(e) {
				var btn = $(this);
				var action = "review";
				if (btn.hasClass("reviewed")) {
					action = "unreview";
				}
				$.ajax({
				url : ENTRADA_URL + "/api/eportfolio.api.php",
					type : "POST",
					data : "method=pentry-review&action="+action+"&pentry_id=" + btn.data("pentry-id"),
					success: function(data) {
						var jsonResponse = JSON.parse(data);
						if (jsonResponse.data.reviewed_date > 0) {
							btn.addClass("reviewed").html("<i class=\"icon-check\"></i> Reviewed");
						} else {
							btn.removeClass("reviewed").html("<i class=\"icon-check\"></i> Review");
						}
					}
				});
				e.preventDefault();
			});
			$("#user-portfolio").on("click", ".comment-delete", function(e) {
				var btn = $(this);
				$.ajax({
				url : ENTRADA_URL + "/api/eportfolio.api.php",
					type : "POST",
					data : "method=delete-pentry-comment&pecomment_id=" + btn.data("pecomment-id"),
					success: function(data) {
						var jsonResponse = JSON.parse(data);
						if (jsonResponse.status == "success") {
							btn.closest(".comment").remove();
						}
					}
				});
				e.preventDefault();
			});
			
			$("#manage").on("click", ".portfolio-item", function(e) {
				var btn = $(this);
				$("#manage-eportfolio-title").html(btn.html())
				$("#artifacts").empty();
				$.ajax({
					url : ENTRADA_URL + "/api/eportfolio.api.php",
					type : "GET",
					data : "method=get-folders&portfolio_id=" + btn.data("portfolio-id"),
					success: function(data) {
						var jsonResponse = JSON.parse(data);
						if (jsonResponse.status == "success") {
							$.each(jsonResponse.data, function(i, v) {
								
								var folder_container = document.createElement("div");
								var folder_title = document.createElement("h3");
								var folder_desc = document.createElement("p");
								$(folder_title).html(v.title);
								$(folder_title).append(" <a href=\"#manage-modal\" data-toggle=\"modal\" class=\"add-artifact\" data-pfolder-id=\""+v.pfolder_id+"\"><i class=\"icon-plus-sign\"></i></a> <a href=\"#manage-modal\" data-toggle=\"modal\" class=\"edit-folder\" data-pfolder-id=\""+v.pfolder_id+"\"><i class=\"icon-edit\"></i></a> <a href=\"#manage-modal\" data-toggle=\"modal\" class=\"delete-folder\" data-pfolder-id=\""+v.pfolder_id+"\"><i class=\"icon-trash\"></i></a>");
								$(folder_desc).html(v.description);
								
								var artifacts_container = document.createElement("div");
								$(artifacts_container).addClass("well").attr("data-pfolder-id", v.pfolder_id);
								var artifacts = document.createElement("ul");
								$(artifacts).addClass("artifacts");
								$.ajax({
									url : ENTRADA_URL + "/api/eportfolio.api.php",
										type : "GET",
										data : "method=get-folder-artifacts&pfolder_id=" + v.pfolder_id + "&proxy_id=0",
										async: false,
										success: function(data) {
											var artifactJsonResponse = JSON.parse(data);
											if (artifactJsonResponse.status == "success") {
												$.each(artifactJsonResponse.data, function(a_i, a_v) {
													$(artifacts).append("<li data-id=\""+a_v.pfartifact_id+"\"><strong>" + a_v.title + "</strong> <a href=\"#manage-modal\" data-toggle=\"modal\" class=\"edit-artifact\" data-id=\""+a_v.pfartifact_id+"\"><i class=\"icon-edit\"></i></a> <a href=\"#manage-modal\" data-toggle=\"modal\" class=\"delete-artifact\" data-id=\""+a_v.pfartifact_id+"\"><i class=\"icon-trash\"></i></a><div>" + a_v.description + "</div></li>")
												});
											} else {
												$(artifacts).append("<li><strong>No artifacts in this folder</strong></li>");
											}
										}
								});
								$(artifacts_container).append(artifacts);
								$(folder_container).append(folder_title).append(folder_desc).append(artifacts_container);
								$("#artifacts").append(folder_container);
							});
						}
					}
				});
				e.preventDefault();
			});
			$("#manage").on("click", ".add-artifact", function(e) {
				$("#manage-modal .modal-footer .save-btn").addClass("btn-primary").removeClass("btn-danger").html("Save");
				var btn = $(this);
				$("#portfolio-form").empty()
				$("#display-error-box-modal").remove();
				adminArtifactForm(btn);
				e.preventDefault();
			});
			$("#manage").on("click", ".edit-artifact", function(e) {
				$("#manage-modal .modal-footer .save-btn").addClass("btn-primary").removeClass("btn-danger").html("Save");
				var btn = $(this);
				$("#portfolio-form").empty()
				$("#display-error-box-modal").remove();
				adminArtifactForm(btn);
				
				$.ajax({
					url : ENTRADA_URL + "/api/eportfolio.api.php",
					type : "GET",
					data : "method=get-folder-artifact&pfartifact_id="+btn.data("id"),
					success: function(data) {
						var jsonResponse = JSON.parse(data);
						if (jsonResponse.status == "success") {
							$("#portfolio-form input[name='pfolder_id']").attr("value", jsonResponse.data.pfolder_id);
							$("#portfolio-form").append("<input type=\"hidden\" name=\"pfartifact_id\" value=\""+jsonResponse.data.pfartifact_id+ "\" />")
							$("#artifact-title").attr("value", jsonResponse.data.title);
							$("#artifact-description").attr("value", jsonResponse.data.description);
							var start_date = new Date(jsonResponse.data.start_date * 1000);
							$("#start_date").attr("value", start_date.getFullYear() + "-" + (start_date.getMonth() <= 9 ? "0" : "") + (start_date.getMonth() + 1) + "-" +  (start_date.getDate() <= 9 ? "0" : "") + start_date.getDate());
							var finish_date = new Date(jsonResponse.data.finish_date * 1000);
							$("#finish_date").attr("value", finish_date.getFullYear() + "-" + (finish_date.getMonth() <= 9 ? "0" : "") + (finish_date.getMonth() + 1) + "-" +  (finish_date.getDate() <= 9 ? "0" : "") + finish_date.getDate());
							if (jsonResponse.data.allow_commenting == 1) {
								$("#allow_commenting").attr("checked", "checked");
							}
							$("#artifact-description").ckeditor();
						}
					}
				})
				
				e.preventDefault();
			});
			$("#manage").on("click", ".delete-artifact, .delete-folder", function(e) {
				var btn = $(this);
				$("#portfolio-form").empty();
				$("#display-error-box-modal").remove();
				if (btn.hasClass("delete-artifact")) {
					$("#manage-modal .modal-header h3").html("Delete Artifact");
					var modal_btn = $("#manage-modal .modal-footer .btn-primary");
					modal_btn.removeClass("btn-primary").addClass("btn-danger").html("Delete").attr("data-pfartifact-id", btn.data("id"));
					display_error(["<strong>Warning</strong>, you have clicked the delete artifact button. <br/><br /> Please confirm you wish to delete the artifact by clicking on the button below."], "#manage-modal .modal-body", "append");
				} else if (btn.hasClass("delete-folder")) {
					$("#manage-modal .modal-header h3").html("Delete Folder");
					var modal_btn = $("#manage-modal .modal-footer .btn-primary");
					modal_btn.removeClass("btn-primary").addClass("btn-danger").html("Delete").attr("data-pfolder-id", btn.data("pfolder-id"));
					display_error(["<strong>Warning</strong>, you have clicked the delete folder button. <br/><br /> Please confirm you wish to delete the folder by clicking on the button below. All artifacts will also be deleted."], "#manage-modal .modal-body", "append");
				}
				e.preventDefault();
			});
			$("#manage-modal .modal-footer .save-btn").on("click", function(e) {
				var btn = $(this);
				if ($(this).hasClass("btn-danger")) {
					
					var method = "delete-artifact";
					var datatype = "pfartifact_id";
					var data = btn.data("pfartifact-id");
					console.log(btn.data("pfolder-id"));
					if (typeof btn.data("pfolder-id") != "undefined") {
						method = "delete-folder"
						datatype = "pfolder_id";
						data = btn.data("pfolder-id");
					}
					
					$.ajax({
						url : ENTRADA_URL + "/api/eportfolio.api.php",
						type : "POST",
						data : "method=" + method + "&" + datatype + "=" + data,
						success: function(data) {
							var jsonResponse = JSON.parse(data);
							if (jsonResponse.status == "success") {
								$("ul.artifacts li[data-id='" + btn.data("pfartifact-id") + "']").remove();
								$("#manage-modal").modal("hide");
							}
						}
					});
				} else {
					$.ajax({
						url : ENTRADA_URL + "/api/eportfolio.api.php",
						type : "POST",
						data : $(".admin-portfolio-form").serialize(),
						success: function(data) {
							var jsonResponse = JSON.parse(data);
							if (jsonResponse.status == "success") {
								if ($("div[data-pfolder-id='" + jsonResponse.data.pfolder_id + "'] ul li[data-id='"+jsonResponse.data.pfartifact_id+"']").length > 0) {
									$("div[data-pfolder-id='" + jsonResponse.data.pfolder_id + "'] ul li[data-id='"+jsonResponse.data.pfartifact_id+"']").html("<strong>" + jsonResponse.data.title + "</strong> <a href=\"#manage-modal\" data-toggle=\"modal\" class=\"edit-artifact\" data-id=\""+jsonResponse.data.pfartifact_id+"\"><i class=\"icon-edit\"></i></a> <a href=\"#manage-modal\" data-toggle=\"modal\" class=\"delete-artifact\" data-id=\""+jsonResponse.data.pfartifact_id+"\"><i class=\"icon-trash\"></i></a><div>" + jsonResponse.data.description + "</div>");
								} else {
									$("div[data-pfolder-id='" + jsonResponse.data.pfolder_id + "'] ul").append("<li data-id=\""+jsonResponse.data.pfartifact_id+"\"><strong>" + jsonResponse.data.title + "</strong> <a href=\"#manage-modal\" data-toggle=\"modal\" class=\"edit-artifact\" data-id=\""+jsonResponse.data.pfartifact_id+"\"><i class=\"icon-edit\"></i></a> <a href=\"#manage-modal\" data-toggle=\"modal\" class=\"delete-artifact\" data-id=\""+jsonResponse.data.pfartifact_id+"\"><i class=\"icon-trash\"></i></a><div>" + jsonResponse.data.description + "</div></li>");
								}
								$("#manage-modal").modal("hide");
							}
						}
					});
				}
				e.preventDefault();
			});
		});
	</script>
	<style type="text/css">
		.tab-content.visible {
			overflow: visible;
		}
		.pane-container {
			border:1px solid #DDDDDD;
			height: 500px;
			border-radius:5px;
		}
		
		.left-pane, .right-pane {
			overflow-y:scroll;
			overflow-x:hidden;
			height: 500px;
		}
		
		.left-pane ul {
			list-style: none;
			margin:0;
			padding:0;
		}
		
		.left-pane ul li {
			margin:0px;
			padding:0px;
		}
		
		.left-pane ul li a {
			display:block;
			padding:12px 10px;
		}
		
		.left-pane ul li a.active {
			background: #f9f9f9; /* Old browsers */
			background: -moz-linear-gradient(top,  #f9f9f9 0%, #f2f2f2 100%); /* FF3.6+ */
			background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f9f9f9), color-stop(100%,#f2f2f2)); /* Chrome,Safari4+ */
			background: -webkit-linear-gradient(top,  #f9f9f9 0%,#f2f2f2 100%); /* Chrome10+,Safari5.1+ */
			background: -o-linear-gradient(top,  #f9f9f9 0%,#f2f2f2 100%); /* Opera 11.10+ */
			background: -ms-linear-gradient(top,  #f9f9f9 0%,#f2f2f2 100%); /* IE10+ */
			background: linear-gradient(to bottom,  #f9f9f9 0%,#f2f2f2 100%); /* W3C */
			filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f9f9f9', endColorstr='#f2f2f2',GradientType=0 ); /* IE6-9 */
			border-bottom: none;
		}
		
		.right-pane {
			padding-right:2.12766%;
		}
		
		.right-pane ul {
			list-style:none;
			margin:0px;
			padding:0px;
		}
		
		.right-pane .portfolio-folder {
			display:block;
			padding:12px 10px;
		}
		
		.well.comments {
			background:#fff;
		}
		#ui-datepicker-div {
			z-index:1050!important;
		}
	</style>
	<ul class="nav nav-tabs">
		<li><a href="#review" data-toggle="tab">Review</a></li>
		<li class="active"><a href="#manage" data-toggle="tab">Manage</a></li>
		<li><a href="#advisors" data-toggle="tab">Advisors</a></li>
    </ul>
	
	<div class="tab-content visible">
		<div class="tab-pane" id="review">
			<div class="row-fluid space-below">
				<div class="btn-group">
					<a class="btn btn-primary">Year</a>
					<a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>
					<ul class="dropdown-menu" id="portfolio-list">
					<?php foreach ($eportfolios as $eportfolio) { ?>
						<li>
							<a href="#" data-id="<?php echo $eportfolio->getID(); ?>" class="portfolio-item"><?php echo $eportfolio->getPortfolioName(); ?></a>
						</li>
					<?php } ?>
					</ul>
				</div>
				<div class="btn-group" id="flag-toggle">
					<button type="button" class="btn active">All</button>
					<button type="button" class="btn flagged">Flagged</button>
				</div>
			</div>
			<div id="breadcrumb" class="row-fluid space-below"></div>
			<div id="portfolio-container" class="pane-container row-fluid">
				<div id="user-list" class="left-pane span3"></div>
				<div id="user-portfolio" class="right-pane span9">
					<h1>Portfolio</h1>
					<?php echo display_generic("Please select a year from the dropdown above to get started."); ?>
				</div>
			</div>
			<div id="entry-modal" class="modal hide">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>View Entry</h3>
				</div>
				<div class="modal-body">
					<form action="" method="POST" class="form-horizontal" id="modal-form"></form>
				</div>
				<div class="modal-footer">
					<a href="#" class="btn">Close</a>
					<a href="#" class="btn btn-primary">Save</a>
				</div>
			</div>
		</div>
		<div class="tab-pane active" id="manage">
			<div class="pane-container row-fluid">
				<div class="left-pane span3">
					<ul>
					<?php foreach ($eportfolios as $eportfolio) { ?>
						<li><a href="#" class="portfolio-item" data-portfolio-id="<?php echo $eportfolio->getID(); ?>"><?php echo $eportfolio->getPortfolioName(); ?></a></li>
					<?php } ?>
					</ul>
				</div>
				<div class="right-pane span9">
					<h1 id="manage-eportfolio-title">Manage Eportfolio</h1>
					<div class="btn-group">
						<a href="#manage-modal" data-toggle="modal" class="btn add-folder"><i class="icon-folder-open" title="Edit"></i> Add Folder</a>
						<button class="btn dropdown-toggle" data-toggle="dropdown">
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							<li><a href="#"><i class="icon-edit" title="Edit"></i> Edit Portfolio</a></li>
							<li><a href="#"><i class="icon-refresh" title="Copy"></i> Copy Portfolio</a></li>
							<li><a href="#"><i class="icon-trash" title="Delete"></i> Delete Portfolio</a></li> 
						</ul>
					</div>
					<div id="artifacts"></div>
				</div>
				<div id="manage-modal" class="modal hide">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3>View Entry</h3>
					</div>
					<div class="modal-body">
						<form action="" method="POST" class="form-horizontal admin-portfolio-form" id="portfolio-form"></form>
					</div>
					<div class="modal-footer">
						<a href="#" class="btn" data-dismiss="modal" aria-hidden="true">Close</a>
						<a href="#" class="btn btn-primary save-btn">Save changes</a>
					</div>
				</div>
			</div>
		</div>
		<div class="tab-pane" id="advisors">
			
		</div>
	</div>
	<?php
}

