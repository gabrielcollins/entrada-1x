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
	$eportfolios = Models_Eportfolio::fetchAll();
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
														$(comment_container).addClass("comments well space-above").html("<strong>Comments:</strong><hr />");
														$.each(v.comments, function(c_i, comment) {
															$(comment_container).append("&ldquo;"+comment.comment+"&rdquo;<br /><span class=\"muted content-small\">"+comment.commentor+" - "+comment.submitted_date+"</span><hr />");
														});
														$(entry_row).append(comment_container);
													}
													
													var entry_controls = document.createElement("div");
													$(entry_controls).addClass("row-fluid space-above");
													
													var flag_btn = document.createElement("button");
													$(flag_btn).addClass("btn btn-danger btn-mini pull-right add-flag space-right").attr("data-pentry-id", v.pentry_id).html("<i class=\"icon-flag icon-white\"></i> Flag")
													
													
													var comment_btn = document.createElement("button");
													$(comment_btn).addClass("btn btn-success btn-mini pull-right add-comment").attr("data-pentry-id", v.pentry_id).html("<i class=\"icon-plus-sign icon-white\"></i> Add Comment")
													
													$(entry_controls).append(comment_btn);
													$(entry_controls).append(flag_btn);
													
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
							
						}
					}
				});

				e.preventDefault();
			});
			$("#entry-modal .modal-footer .btn-primary").on("click", function(e) {
				$("#modal-form").submit();
				e.preventDefault();
			});
		});
	</script>
	<style type="text/css">
		#portfolio-container {
			border:1px solid #DDDDDD;
			height: 500px;
			border-radius:5px;
		}
		
		#user-list, #user-portfolio {
			overflow-y:scroll;
			overflow-x:hidden;
			height: 500px;
		}
		
		#user-list ul {
			list-style: none;
			margin:0;
			padding:0;
		}
		
		#user-list ul li {
			margin:0px;
			padding:0px;
		}
		
		#user-list ul li a {
			display:block;
			padding:12px 10px;
		}
		
		#user-list ul li a.active {
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
		
		#user-portfolio {
			padding-right:2.12766%;
		}
		
		#user-portfolio ul {
			list-style:none;
			margin:0px;
			padding:0px;
		}
		
		#user-portfolio .portfolio-folder {
			display:block;
			padding:12px 10px;
		}
		
		.well.comments {
			background:#fff;
		}
		
	</style>
	<ul class="nav nav-tabs">
		<li class="active"><a href="#">Review</a></li>
		<li><a href="#">Manage</a></li>
		<li><a href="#">Advisors</a></li>
    </ul>
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
	<div id="portfolio-container" class="row-fluid">
		<div id="user-list" class="span3"></div>
		<div id="user-portfolio" class="span9">
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
			<a href="#" class="btn btn-primary">Save changes</a>
		</div>
	</div>
	<?php
	/*$folders = $eportfolio->getFolders();
	?>
	
	<h2><?php echo $eportfolio->getPortfolioName(); ?></h2>
	<div class="btn-group">
		<a class="btn btn-primary">Folders</a>
		<a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>
		<ul class="dropdown-menu" id="folder-list">
		<?php 
		foreach ($folders as $folder) { ?>*
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
				<input type="hidden" value="create-artifact" class="method" name="content-type" id="method" />
			</form>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn pull-left" data-dismiss="modal">Cancel</a>
			<a href="#" class="btn btn-primary" id="save-button">Save changes</a>
		</div>
	</div>
	<?php
	/*
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
	*/
}

