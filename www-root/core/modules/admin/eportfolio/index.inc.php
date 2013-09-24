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
	$HEAD[] = "<script type=\"text/javascript\">var ENTRADA_URL = '".ENTRADA_URL."'; var PROXY_ID = '".$ENTRADA_USER->getProxyId()."';</script>";
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
				data: "method=get-portfolio-members&portfolio_id=" + portfolio_id,
				type: 'GET',
				success:function (data) {
					var jsonResponse = JSON.parse(data);
					if (jsonResponse.status == "success") {
						jQuery("#portfolio-container").html("");
						var user_list = document.createElement("ul");
						for (var i=0; i < jsonResponse.data.length; i++) {
							var user_row = document.createElement("li");
							var user_link = document.createElement("a");
							jQuery(user_link).addClass("portfolio-user");
							jQuery(user_link).attr("data-proxy-id", jsonResponse.data[i].proxy_id).attr("data-portfolio-id", portfolio_id).attr("href", "#").html(jsonResponse.data[i].lastname + ", " + jsonResponse.data[i].firstname);
							jQuery(user_row).append(user_link);
							jQuery(user_list).append(user_row);
						}
						jQuery("#portfolio-container").append(user_list);
					}
				}
			});
		}
		
		function getFolders(portfolio_id) {
			jQuery.ajax({
				url: ENTRADA_URL + "/api/eportfolio.api.php",
				data: "method=get-folders&portfolio_id=" + portfolio_id,
				type: 'GET',
				success:function (data) {
					var jsonResponse = JSON.parse(data);
					if (jsonResponse.status == "success") {
						jQuery("#portfolio-container").html("");
						var folder_list = document.createElement("ul");
						jQuery.each(jsonResponse.data, function(i, v) {
							var folder_row = document.createElement("li");
							var folder_link = document.createElement("a");
							jQuery(folder_link).addClass("portfolio-folder");
							jQuery(folder_link).attr("data-pfolder-id", v.pfolder_id).attr("href", "#").html(v.title);
							jQuery(folder_row).append(folder_link);
							jQuery(folder_list).append(folder_row);
						});
						jQuery("#portfolio-container").append(folder_list);
					}
				}
			});
		}
		
		jQuery(function($) {
			$(".portfolio-item").on("click", function (e) {
				portfolio_id = $(this).data("id");
				getPortfolio(portfolio_id);
				location.hash = $(this).attr("data-id");
				
				e.preventDefault();
			});
			$("#portfolio-container").on("click", ".portfolio-user", function(e) {
				PROXY_ID = $(this).data("proxy-id");
				portfolio_id = $(this).data("portfolio-id");
				getFolders(portfolio_id);
			});
			$("#portfolio-container").on("click", ".portfolio-folder", function(e) {
				var pfolder_id = $(this).data("pfolder-id");
				var proxy_id = PROXY_ID;
				$.ajax({
					url: ENTRADA_URL + "/api/eportfolio.api.php",
					data: "method=get-folder-artifacts&pfolder_id=" + pfolder_id + "&proxy_id=" + proxy_id,
					type: 'GET',
					success:function (data) {
						var jsonResponse = JSON.parse(data);
						var artifact_list = document.createElement("ul");
						$("#portfolio-container").html("");
						$.each(jsonResponse.data, function(i, v) {
							var artifact_row = document.createElement("li");
							var artifact_title = document.createElement("h3");
							var pfartifact_id = v.pfartifact_id;
							$(artifact_title).html(v.title);
							var entries = document.createElement("ul");
							$.ajax({
								url : ENTRADA_URL + "/api/eportfolio.api.php",
								data : "method=get-artifact-entries&pfartifact_id=" + pfartifact_id + "&proxy_id=" + proxy_id,
								type : 'GET',
								success : function (data) {
									var entryJsonResponse = JSON.parse(data);
									$.each(entryJsonResponse.data, function(i, v) {
										var entry_row = document.createElement("li");
										if (typeof v._edata.title != 'undefined' && v._edata.description.title > 0) {
											$(entry_row).append("<h4>" + v._edata.title + "</h4>");
										}
										if (typeof v._edata.filename != 'undefined' && v._edata.filename.length > 0) {
											var entry_link = document.createElement("a");
											$(entry_link).attr("href", "#").html(v._edata.filename);
											$(entry_row).append(entry_link);
										}
										if (typeof v._edata.description != 'undefined' && v._edata.description.length > 0) {
											$(entry_row).append("<div>" + v._edata.description + "</div>");
										}
										$(entries).append(entry_row);
									});
								}
							});
							
							$(artifact_row).append(artifact_title).append(entries);
							$(artifact_list).append(artifact_row);
						});
						$("#portfolio-container").append(artifact_list);
					}
				});
			});
		});
	</script>
	<ul class="nav nav-tabs">
		<li class="active"><a href="#">Manage Learners</a></li>
		<li><a href="#">Manage Portfolios</a></li>
    </ul>
	<div id="breadcrumb">
		<a href="#">Class of 2016</a> / <a href="#">Bowen, Milton</a> / Collaborator
	</div>
	<div class="btn-group">
		<a class="btn btn-primary">Portfolios</a>
		<a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>
		<ul class="dropdown-menu" id="portfolio-list">
		<?php foreach ($eportfolios as $eportfolio) { ?>
			<li>
				<a href="#" data-id="<?php echo $eportfolio->getID(); ?>" class="portfolio-item"><?php echo $eportfolio->getPortfolioName(); ?></a>
			</li>
		<?php } ?>
		</ul>
	</div>
	<div id="portfolio-container"></div>
	<div id="entry-modal">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>View Entry</h3>
		</div>
		<div class="modal-body"></div>
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

