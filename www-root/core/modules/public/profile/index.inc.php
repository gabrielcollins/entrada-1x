<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * This file gives Entrada users the ability to update their user profile.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/
if (!defined("IN_PROFILE")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->isLoggedInAllowed('profile', 'read')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {

	$ajax_action = clean_input($_POST["ajax_action"], "alpha");

	if ($ajax_action == "uploadimageie") {
		$file_data = getimagesize($_FILES["image"]["tmp_name"]);
		$file_dimensions = $file_data[0] . "," . $file_data[1];
		
		$aspect_ratio = $file_data[0] / $file_data[1];
		if ($aspect_ratio >= 0.76) {
			$offset = round(($file_data[0] - ($file_data[0] * .76)) / 2);
			$coordinates = $offset . ",0,".($offset + round($file_data[0] * .76)).",".$file_data[1];
		} else {
			$offset = round(($file_data[1] - ($file_data[1] * .76)) / 2);
			$coordinates =  "0,".$offset.",".$file_data[0].",".($offset + round($file_data[1] * .76));
		}
		
		if ($coordinates) {
			$coords = explode(",", $coordinates);
			foreach($coords as $coord) {
				$tmp_coords[] = clean_input($coord, "int");
			}
			$PROCESSED["coordinates"] = implode(",", $tmp_coords);
		}
		if ($file_dimensions) {
			$dimensions = explode(",", $file_dimensions);
			foreach($dimensions as $dimension) {
				$tmp_dimensions[] = clean_input($dimension, "int");
			}
			$PROCESSED["dimensions"] = implode(",", $tmp_dimensions);
		}		
		$filesize = moveImage($_FILES["image"]["tmp_name"], $ENTRADA_USER->getID(), $PROCESSED["coordinates"], $PROCESSED["dimensions"]);

		if ($filesize) {
			$PROCESSED_PHOTO["proxy_id"]			= $ENTRADA_USER->getID();
			$PROCESSED_PHOTO["photo_active"]		= 1;
			$PROCESSED_PHOTO["photo_type"]			= 1;
			$PROCESSED_PHOTO["updated_date"]		= time();
			$PROCESSED_PHOTO["photo_filesize"]		= $filesize;

			$query = "SELECT `photo_id` FROM `".AUTH_DATABASE."`.`user_photos` WHERE `proxy_id` = ".$db->qstr($ENTRADA_USER->getID());
			$photo_id = $db->GetOne($query);
			if ($photo_id) {
				if ($db->AutoExecute("`".AUTH_DATABASE."`.`user_photos`", $PROCESSED_PHOTO, "UPDATE", "`photo_id` = ".$db->qstr($photo_id))) {
					add_success("Your profile image has been successfully uploaded.");
				}
			} else {
				if ($db->AutoExecute("`".AUTH_DATABASE."`.`user_photos`", $PROCESSED_PHOTO, "INSERT")) {
					add_success("Your profile image has been successfully uploaded.");
				} else {
					add_error("An error ocurred while attempting to update your profile photo record, please try again later.");
				}
			}
		} else {
			add_error("An error ocurred while moving your image in the system, please try again later.");
		}
	}
	
	if (!empty($ajax_action) && $ajax_action != "uploadimageie") {
		
		ob_clear_open_buffers();

		switch ($ajax_action) {
			case "uploadimage" :

				if ($_POST["coordinates"]) {
					$coords = explode(",", $_POST["coordinates"]);
					foreach($coords as $coord) {
						$tmp_coords[] = clean_input($coord, "int");
					}
					$PROCESSED["coordinates"] = implode(",", $tmp_coords);
				}
				if ($_POST["dimensions"]) {
					$dimensions = explode(",", $_POST["dimensions"]);
					foreach($dimensions as $dimension) {
						$tmp_dimensions[] = clean_input($dimension, "int");
					}
					$PROCESSED["dimensions"] = implode(",", $tmp_dimensions);
				}
				
				$filesize = moveImage($_FILES["image"]["tmp_name"], $ENTRADA_USER->getID(), $PROCESSED["coordinates"], $PROCESSED["dimensions"]);

				if ($filesize) {
					$PROCESSED_PHOTO["proxy_id"]			= $ENTRADA_USER->getID();
					$PROCESSED_PHOTO["photo_active"]		= 1;
					$PROCESSED_PHOTO["photo_type"]			= 1;
					$PROCESSED_PHOTO["updated_date"]		= time();
					$PROCESSED_PHOTO["photo_filesize"]		= $filesize;

					$query = "SELECT `photo_id` FROM `".AUTH_DATABASE."`.`user_photos` WHERE `proxy_id` = ".$db->qstr($ENTRADA_USER->getID());
					$photo_id = $db->GetOne($query);

					if ($photo_id) {
						if ($db->AutoExecute("`".AUTH_DATABASE."`.`user_photos`", $PROCESSED_PHOTO, "UPDATE", "`photo_id` = ".$db->qstr($photo_id))) {
							echo json_encode(array("status" => "success", "data" => webservice_url("photo", array($ENTRADA_USER->getID(), "upload"))."/".time()));
						}
					} else {
						if ($db->AutoExecute("`".AUTH_DATABASE."`.`user_photos`", $PROCESSED_PHOTO, "INSERT")) {
							echo json_encode(array("status" => "success", "data" => webservice_url("photo", array($ENTRADA_USER->getID(), "upload"))."/".time()));
						} else {
							echo json_encode(array("status" => "error"));
						}
					}
				}
			break;
			case "togglephoto" :
				$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_photos` WHERE `proxy_id` = ".$db->qstr($ENTRADA_USER->getID());
				$photo_record = $db->GetRow($query);
				if ($photo_record) {
					$photo_active = ($photo_record["photo_active"] == "1" ? "0" : "1");
					$query = "UPDATE `".AUTH_DATABASE."`.`user_photos` SET `photo_active` = ".$db->qstr($photo_active)." WHERE `proxy_id` = ".$db->qstr($ENTRADA_USER->getID());
					if ($db->Execute($query)) {
						echo json_encode(array("status" => "success", "data" => array("imgurl" => webservice_url("photo", array($ENTRADA_USER->getID(), $photo_active == "1" ? "upload" : "official" ))."/".time(), "imgtype" => $photo_active == "1" ? "uploaded" : "official")));
					} else {
						application_log("error", "An error occurred while attempting to update user photo active flag for user [".$ENTRADA_USER->getID()."], DB said: ".$db->ErrorMsg());
						echo json_encode(array("status" => "error"));
					}
				} else {
					echo json_encode(array("status" => "error", "data" => "No uploaded photo record on file. You must upload a photo before you can toggle photos."));
				}
			break;
			case "generatehash" :
				$new_private_hash = generate_hash();
				$query = "UPDATE IGNORE `".AUTH_DATABASE."`.`user_access` SET `private_hash` = ".$db->qstr($new_private_hash)." WHERE `user_id` = ".$db->qstr($ENTRADA_USER->getID())." AND `organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation());
				$result = $db->Execute($query);
				if ($result) {
					echo json_encode(array("status" => "success", "data" => $new_private_hash));
					$_SESSION["details"]["private_hash"] = $new_private_hash;
				} else {
					echo json_encode(array("status" => "error"));
				}
			break;
			case "resetpw" :
				
				if ($_POST["current_password"] && $tmp_input = clean_input($_POST["current_password"], array("trim", "striptags"))) {
					$PROCESSED["current_password"] = $tmp_input;
				}
				
				if ($_POST["new_password"] && $tmp_input = clean_input($_POST["new_password"], array("trim", "striptags"))) {
					$PROCESSED["new_password"] = $tmp_input;
				} else {
					$err[] = "An invalid password was provided.";
				}
				
				if ($_POST["new_password_confirm"] && $tmp_input = clean_input($_POST["new_password_confirm"], array("trim", "striptags"))) {
					$PROCESSED["new_password_confirm"] = $tmp_input;
				} else {
					$err[] = "An invalid password was provided.";
				}
				
				if ($PROCESSED["new_password"] !== $PROCESSED["new_password_confirm"]) {
					$errs[] = "New password dosen't match!";
				}
				
				$query	= "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($ENTRADA_USER->getID())." AND ((`salt` IS NULL AND `password` = MD5(".$db->qstr($PROCESSED["current_password"]).")) OR (`salt` IS NOT NULL AND `password` = SHA1(CONCAT(".$db->qstr($PROCESSED["current_password"]).", `salt`))))";
				$result	= $db->GetRow($query);
				if ($result) {
					if (!$errs) {
						$user_password = $PROCESSED["new_password"];
						/**
						 * Check to see if password requires some updating.
						 */
						if (!$result["salt"]) {
							$salt = hash("sha256", (uniqid(rand(), 1) . time() . $result["id"]));
						} else {
							$salt = $result["salt"];
						}

						$query = "UPDATE `".AUTH_DATABASE."`.`user_data` SET `password` = ".$db->qstr(sha1($user_password.$salt)).", `salt` = ".$db->qstr($salt)." WHERE `id` = ".$db->qstr($result["id"]);
						if ($db->Execute($query)) {
							application_log("auth_success", "Successfully updated password salt for user [".$result["id"]."] via local auth method.");
							echo json_encode(array("status" => "success", "data" => array("Your password has successfully been updated.")));
						} else {
							application_log("auth_error", "Failed to update password salt for user [".$result["id"]."] via local auth method. Database said: ".$db->ErrorMsg());
							echo json_encode(array("status" => "error", "data" => array("An error ocurred while attempting to update your password. An administrator has been informed, please try again later.")));
						}
					} else {
						echo json_encode(array("status" => "error", "data" => $errs));
					}
				} else {
					echo json_encode(array("status" => "error", "data" => array("The current password did not match the password on file.")));
				}
				
			break;
			default:
			break;
		}

		exit;

	}

	$PAGE_META["title"]			= "My Profile";
	$PAGE_META["description"]	= "";
	$PAGE_META["keywords"]		= "";

	$PROXY_ID					= $ENTRADA_USER->getID();
	$VALID_MIME_TYPES			= array("image/pjpeg" => "jpg", "image/jpeg" => "jpg", "image/jpg" => "jpg", "image/gif" => "gif", "image/png" => "png");
	$VALID_MAX_FILESIZE			= 2097512; // 2MB
	$VALID_MAX_DIMENSIONS		= array("photo-width" => 216, "photo-height" => 300, "thumb-width" => 75, "thumb-height" => 104);
	$RENDER						= false;

	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/profile", "title" => "Personal Information");

	$PROCESSED		= array();

	if ((is_array($_SESSION["permissions"])) && ($total_permissions = count($_SESSION["permissions"]) > 1)) {
		$sidebar_html  = "The following individual".((($total_permissions - 1) != 1) ? "s have" : " has")." given you access to their ".APPLICATION_NAME." permission levels:";
		$sidebar_html .= "<ul class=\"menu\">\n";
		foreach ($_SESSION["permissions"] as $access_id => $result) {
			if ($access_id != $ENTRADA_USER->getDefaultAccessId()) {
				$sidebar_html .= "<li class=\"checkmark\"><strong>".html_encode($result["fullname"])."</strong><br /><span class=\"content-small\">Exp: ".(($result["expires"]) ? date("D M d/y", $result["expires"]) : "Unknown")."</span></li>\n";
			}
		}
		$sidebar_html .= "</ul>\n";

		new_sidebar_item("Delegated Permissions", $sidebar_html, "delegated-permissions", "open");
	}

	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/profile.js\"></script>";

	if (file_exists(STORAGE_USER_PHOTOS."/".$PROXY_ID."-upload")) {
		$size_upload = getimagesize(STORAGE_USER_PHOTOS."/".$PROXY_ID."-upload");
	}
	if (file_exists(STORAGE_USER_PHOTOS."/".$PROXY_ID."-official")) {
		$size_official = getimagesize(STORAGE_USER_PHOTOS."/".$PROXY_ID."-official");
	}

	if ($ERROR) {
		fade_element("out", "display-error-box");
		echo display_error();
	}

	if ($SUCCESS) {
		fade_element("out", "display-success-box");
		echo display_success();
	}

	if ($NOTICE) {
		fade_element("out", "display-notice-box");
		echo display_notice();
	}

	$ONLOAD[] = "provStateFunction(\$F($('profile-update')['country_id']))";

	$query	= "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `".AUTH_DATABASE."`.`user_data`.`id`=".$db->qstr($ENTRADA_USER->getID());
	$result	= $db->GetRow($query);
	if ($result) {
		/*
		 * Get the user departments and the custom fields for the departments.
		 */
		$user_departments = get_user_departments($ENTRADA_USER->getID());
		foreach ($user_departments as $department) {
			$departments[$department["department_id"]] = $department["department_title"];
		}

		$custom_fields = fetch_department_fields();

		?>
		<script type="text/javascript">
		function provStateFunction(country_id) {
			var url='<?php echo webservice_url("province"); ?>';
			<?php
				if ($PROCESSED["province"] || $PROCESSED["province_id"]) {
					$source_arr = $PROCESSED;
				} else {
					$source_arr = $result;
				}
				$province = $source_arr["province"];
				$province_id = $source_arr["province_id"];
				$prov_state = ($province) ? $province : $province_id;
			?>

			url = url + '?countries_id=' + country_id + '&prov_state=<?php echo $prov_state; ?>';
			new Ajax.Updater($('prov_state_div'), url,
				{
					method:'get',
					onComplete: function (init_run) {

						if ($('prov_state').type == 'select-one') {
							$('prov_state_label').removeClassName('form-nrequired');
							$('prov_state_label').addClassName('form-required');
							if (!init_run)
								$("prov_state").selectedIndex = 0;

						} else {

							$('prov_state_label').removeClassName('form-required');
							$('prov_state_label').addClassName('form-nrequired');
							if (!init_run)
								$("prov_state").clear();

						}
					}.curry(!provStateFunction.initialzed)
				});
			provStateFunction.initialzed = true;

		}
		provStateFunction.initialzed = false;

		</script>

		<h1 style="margin-top: 0px">Personal Information</h1>
		<div id="msgs">
			
		</div>
		This section allows you to update your <?php echo APPLICATION_NAME; ?> user profile information. Please note that this information does not necessarily reflect any information stored at the main University. <span style="background-color: #FFFFCC; padding-left: 5px; padding-right: 5px">This is not your official university contact information.</span>
		<br /><br />
		<div id="profile-wrapper">
		<script src="<?php echo ENTRADA_URL; ?>/javascript/jquery/jquery.imgareaselect.min.js" type="text/javascript"></script>
		<link href='<?php echo ENTRADA_URL; ?>/css/imgareaselect-default.css' rel='stylesheet' type='text/css' />
		<style type="text/css">
			.table-nowrap {white-space:nowrap;}
			#profile-wrapper {position:relative;}
			#upload_profile_image_form {margin:0px;float:left;}
			#profile-image-container {position:absolute;right:0px;}
			#upload-image-modal-btn {position:absolute;right:7px;top:7px;display:none;outline:none;}
			#btn-toggle {position:absolute;right:7px;bottom:7px;display:none;outline:none;}
			#btn-toggle .btn {outline:none;}
			.profile-image-preview {text-align:center;max-width:275px;margin:auto;}
			.modal-body {max-height:none;}
		</style>
		<?php $profile_image = ENTRADA_ABSOLUTE . '/../public/images/' . $ENTRADA_USER->getID() . '/' . $ENTRADA_USER->getID() . '-large.png'; ?>
		<script type="text/javascript">
		function dataURItoBlob(dataURI) {
			var byteString = atob(dataURI.split(',')[1]);
			var ab = new ArrayBuffer(byteString.length);
			var ia = new Uint8Array(ab);
			for (var i = 0; i < byteString.length; i++) {
				ia[i] = byteString.charCodeAt(i);
			}
			return new Blob([ab], { type: 'image/jpeg' });
		}

		jQuery(function(){

			jQuery("#update-pw").on("click", function() {
				jQuery.ajax({
					url : "<?php echo ENTRADA_URL; ?>/profile",
					data : "ajax_action=resetpw&" + jQuery("#update-pw-form").serialize(),
					type : "post",
					async : true,
					success : function(data) {
						var jsonResponse = JSON.parse(data);
						if (jsonResponse.status == "success") {
							jQuery("#password-change-modal").modal("hide");
							display_success(jsonResponse.data, "#msgs");
						} else {
							display_error(jsonResponse.data, "#pw-change-msg");
						}
					}
				});
			});

			jQuery("#password-change-modal").on("hide", function() {
				jQuery("#msgs").html("");
				jQuery("#pw-change-msg").html("");
				jQuery("#current_password, #new_password, #new_password_confirm").attr("value", "");
			});

			jQuery("#reset-hash").live("click", function() {
				jQuery.ajax({
					url : "<?php echo ENTRADA_URL; ?>/profile",
					data : "ajax_action=generatehash",
					type : "post",
					async : true,
					success : function(data) {
						var jsonResponse = JSON.parse(data);
						jQuery("#hash-value").html(jsonResponse.data);
					}
				});
				jQuery("#reset-hash-modal").modal("hide");
			});
			
			jQuery("#btn-toggle .btn").live("click", function() {
				var clicked = jQuery(this);
				if (!clicked.parent().hasClass(clicked.html().toLowerCase())) {
					jQuery.ajax({
						url : "<?php echo ENTRADA_URL; ?>/profile",
						data : "ajax_action=togglephoto",
						type : "post",
						async : true,
						success : function(data) {
							var jsonResponse = JSON.parse(data);
							jQuery("#profile-image-container span img").attr("src", jsonResponse.data.imgurl);
							jQuery("#btn-toggle .btn.active").removeClass("active");
							clicked.addClass("active");
							clicked.parent().removeClass((jsonResponse.data.imgtype == "uploaded" ? "official" : "uploaded")).addClass(jsonResponse.data.imgtype);
						}
					});
				}
				return false;
			});

			function selectImage(image){
				jQuery(".description").hide();
				var image_width;
				var image_height;
				var w_offset;
				var h_offset

				image_width = image.width();
				image_height = image.height();
				w_offset = parseInt((image_width - 153) / 2);
				h_offset = parseInt((image_height - 200) / 2);

				jQuery("#coordinates").attr("value", w_offset + "," + h_offset + "," + (w_offset + 153) + "," + (h_offset + 200));
				jQuery("#dimensions").attr("value", image_width + "," + image_height)

				image.imgAreaSelect({
					aspectRatio: '75:98',
					handles: true,
					x1: w_offset, y1: h_offset, x2: w_offset + 153, y2: h_offset + 200,
					instance: true,
					persistent: true,
					onSelectEnd: function (img, selection) {
						jQuery("#coordinates").attr("value", selection.x1 + "," + selection.y1 + "," + selection.x2 + "," + selection.y2);
					}
				});
			};

			jQuery(".org-profile-image").hover(function(){
				jQuery(this).find("#edit-button").animate({"opacity" : 100}, {queue: false}, 150).css("display", "block");
			}, function() {
				jQuery(this).find("#edit-button").animate({"opacity" : 0}, {queue: false}, 150);
			});

			/* file upload stuff starts here */
			if (window.FileReader) {
				var reader = new FileReader();

				reader.onload = function (e) {
					jQuery(".preview-image").attr('src', e.target.result)
					jQuery(".preview-image").load(function(){
						selectImage(jQuery(".preview-image"));
					});
				};
			} else {
				jQuery(".preview-img").hide();
				jQuery("#upload-image .description").css("height", "auto");
			}
			
			// Required for drag and drop file access
			jQuery.event.props.push('dataTransfer');

			jQuery("#upload-image").on('drop', function(event) {

				jQuery(".modal-body").css("background-color", "#FFF");

				event.preventDefault();

				var file = event.dataTransfer.files[0];

				if (file.type.match('image.*')) {
					jQuery("#image").html(file);
					if (window.FileReader) {
						reader.readAsDataURL(file);
					}
				} else {
					// However you want to handle error that dropped file wasn't an image
				}
			});

			jQuery("#upload-image").on("dragover", function(event) {
				jQuery(".modal-body").css("background-color", "#f3f3f3");
				return false;
			});

			jQuery("#upload-image").on("dragleave", function(event) {
				jQuery(".modal-body").css("background-color", "#FFF");
			});

			jQuery('#upload-image').on('hidden', function () {
				if (jQuery(".profile-image-preview").length > 0) {
					jQuery(".profile-image-preview").remove();
					jQuery(".imgareaselect-selection").parent().remove();
					jQuery(".imgareaselect-outer").remove();
					jQuery("#image").val("");
					jQuery(".description").show();
				}
			});

			jQuery('#upload-image').on('shown', function() {
				if (!window.FileReader) {
					jQuery("#upload-image .description").html("Your browser does not support image cropping, your image will be center cropped.")
				}
				if (jQuery(".profile-image-preview").length <= 0) {
					var preview = jQuery("<div />").addClass("profile-image-preview");
					preview.append("<img />");
					preview.children("img").addClass("preview-image");
					jQuery(".preview-img").append(preview);
				}
			});

			jQuery("#upload-image-button").live("click", function(){
				if (window.FileReader) {
					if (typeof jQuery(".preview-image").attr("src") != "undefined") {
						jQuery("#upload_profile_image_form").submit();
						jQuery('#upload-image').modal("hide");
					} else {
						jQuery('#upload-image').modal("hide");
					}
				} else {
					jQuery("#upload_profile_image_form").submit();
				}
			});

			jQuery("#upload_profile_image_form").submit(function(){
				if (window.FileReader) {
					var imageFile = dataURItoBlob(jQuery(".preview-image").attr("src"));

					var xhr = new XMLHttpRequest();
					var fd = new FormData();
					fd.append('ajax_action', 'uploadimage');
					fd.append('image', imageFile);
					fd.append('coordinates', jQuery("#coordinates").val());
					fd.append('dimensions', jQuery("#dimensions").val());

					xhr.open('POST', "<?php echo ENTRADA_URL; ?>/profile", true);
					xhr.send(fd);

					xhr.onreadystatechange = function() {
						if (xhr.readyState == 4 && xhr.status == 200) {
							var jsonResponse = JSON.parse(xhr.responseText);
							if (jsonResponse.status == "success") {
								jQuery("#profile-image-container img.img-polaroid").attr("src", jsonResponse.data);
								if (jQuery("#image-nav-right").length <= 0) {
									jQuery("#btn-toggle").append("<a href=\"#\" class=\"btn active\" id=\"image-nav-right\" style=\"display:none;\">Uploaded</a>");
									jQuery("#image-nav-right").removeClass("active");
								}
							} else {
								// Some kind of failure notification.
							};
						} else {
							// another failure notification.
						}
					}

					if (jQuery(".profile-image-preview").length > 0) {
						jQuery(".profile-image-preview").remove();
						jQuery(".imgareaselect-selection").parent().remove();
						jQuery(".imgareaselect-outer").remove();
						jQuery("#image").val("");
						jQuery(".description").show();
					}

					return false;
				} else {
					jQuery("#upload_profile_image_form").append("<input type=\"hidden\" name=\"ajax_action\" value=\"uploadimageie\" />");
				}
			});

			jQuery("#image").live("change", function(){
				var files = jQuery(this).prop("files");

				if (files && files[0]) {
					if (window.FileReader) {
						reader.readAsDataURL(files[0]);
					}
				}
			});

			jQuery("#profile-image-container").hover(function(){
				jQuery("#profile-image-container .btn, #btn-toggle").fadeIn("fast");
			},
			function() {
				jQuery("#profile-image-container .btn").fadeOut("fast");
			});
		});
		</script>
		<div id="profile-image-container">
			<a href="#upload-image" id="upload-image-modal-btn" data-toggle="modal" class="btn btn-primary" id="upload-profile-image">Upload Photo</a>
			<?php
			$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_photos` WHERE `proxy_id` = ".$db->qstr($result["id"]);
			$uploaded_photo = $db->GetRow($query);
			?>
			<span><img src="<?php echo webservice_url("photo", array($ENTRADA_USER->getID(), $uploaded_photo ? "upload" : "official"))."/".time(); ?>" width="192" height="250" class="img-polaroid" /></span>
			<div class="btn-group" id="btn-toggle" class=" <?php echo $uploaded_photo ? "uploaded" : "official"; ?>">
				<a href="#" class="btn btn-small <?php echo $uploaded_photo["photo_active"] == "0" ? "active" : ""; ?>" id="image-nav-left">Official</a>
				<?php if ($uploaded_photo) { ?><a href="#" class="btn btn-small <?php echo $uploaded_photo["photo_active"] == "1" ? "active" : ""; ?>" id="image-nav-right">Uploaded</a><?php } ?>
			</div>
		</div>

		<form class="form-horizontal" name="profile-update" id="profile-update" action="<?php echo ENTRADA_URL; ?>/profile" method="post" enctype="multipart/form-data" accept="<?php echo ((@is_array($VALID_MIME_TYPES)) ? implode(",", array_keys($VALID_MIME_TYPES)) : ""); ?>">
			<input type="hidden" name="action" value="profile-update" />
			<div class="control-group">
				<label class="control-label">Last Login:</label>
				<div class="controls">
					<span class="input-xlarge uneditable-input"><?php echo ((!$_SESSION["details"]["lastlogin"]) ? "Your first login" : date(DEFAULT_DATE_FORMAT, $_SESSION["details"]["lastlogin"])); ?></span>
				</div>
			</div>
			<div class="control-group"></div>
			<div class="control-group">
				<label class="control-label">Username:</label>
				<div class="controls">
					<span class="input-xlarge uneditable-input"><?php echo html_encode($_SESSION["details"]["username"]); ?></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Password:</label>
				<div class="controls">
					<a class="btn btn-link" href="#password-change-modal" data-toggle="modal">Click here to change password</a>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Private Hash:</label>
				<div class="controls">
					<div class="input-append">
						<span class="input-large uneditable-input" id="hash-value"><?php echo $_SESSION["details"]["private_hash"]; ?></span><a class="add-on" href="#reset-hash-modal" data-toggle="modal"><i class="icon-repeat"></i></a>
					</div>

				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Account Type:</label>
				<div class="controls">
					<span class="input-xlarge uneditable-input"><?php echo ucwords($_SESSION["details"]["group"])." <i class=\"icon-arrow-right\"></i> ".ucwords($_SESSION["details"]["role"]); ?></span>
				</div>
			</div>
			<div class="control-group"></div>
			<div class="control-group">
				<label class="control-label">Organisation:</label>
				<div class="controls">
					<span class="input-xlarge uneditable-input">
						<?php
							$query		= "SELECT `organisation_title` FROM `".AUTH_DATABASE."`.`organisations` WHERE `organisation_id` = ".$_SESSION['details']['organisation_id'];
							$oresult	= $db->GetRow($query);
							if($oresult) {
								echo $oresult['organisation_title'];
							}
						?>
					</span>
				</div>
			</div>
			<?php if (isset($_SESSION["details"]["grad_year"])) { ?>
			<div class="control-group">
				<label class="control-label">Graduating Year:</label>
				<div class="controls">
					<span class="input-xlarge uneditable-input">Class of <?php echo html_encode($_SESSION["details"]["grad_year"]); ?></span>
				</div>
			</div>
			<?php } ?>
			<div class="control-group"></div>
			<div class="control-group">
				<label class="control-label" for="prefix">Full Name:</label>
				<div class="controls">
					<select class="inline" id="prefix" name="prefix" style="width: 55px; vertical-align: middle; margin-right: 5px">
						<option value=""<?php echo ((!$result["prefix"]) ? " selected=\"selected\"" : ""); ?>></option>
						<?php
						if ((@is_array($PROFILE_NAME_PREFIX)) && (@count($PROFILE_NAME_PREFIX))) {
							foreach ($PROFILE_NAME_PREFIX as $key => $prefix) {
								echo "<option value=\"".html_encode($prefix)."\"".(($result["prefix"] == $prefix) ? " selected=\"selected\"" : "").">".html_encode($prefix)."</option>\n";
							}
						}
						?>
					</select>
					<span class="help-inline"><?php echo html_encode($result["firstname"]." ".$result["lastname"]); ?></span>
				</div>
			</div>
			<div class="control-group"></div>
			<div class="control-group">
				<label class="control-label" for="email">Primary E-mail:</label>
				<div class="controls">
					<?php if($_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"] == "faculty") { ?>
					<input type="text" class="input-xlarge" id="email" name="email" value="<?php echo html_encode($result["email"]); ?>" style="width: 250px; vertical-align: middle" maxlength="128" />
					<?php } else { ?>
					<span class="input-xlarge uneditable-input"><?php echo html_encode($result["email"]); ?></span>
					<?php } ?>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="email_alt">Secondary E-mail:</label>
				<div class="controls">
					<input class="input-xlarge" name="email_alt" id="email_alt" type="text" placeholder="example@email.com" value="<?php echo html_encode($result["email_alt"]); ?>" />
				</div>
			</div>
			<div class="control-group"></div>
			<?php if (((bool) $GOOGLE_APPS["active"]) && $result["google_id"]) { ?>
			<div class="control-group">
				<label class="control-label">Google Account:</label>
				<div class="controls">

						<?php
						if (($result["google_id"] == "") || ($result["google_id"] == "opt-out") || ($result["google_id"] == "opt-in") || ($_SESSION["details"]["google_id"] == "opt-in")) {
							?>
							Your <?php echo $GOOGLE_APPS["domain"]; ?> account is <strong>not active</strong>. ( <a href="javascript: create_google_account()" class="action">create my account</a> )
							<script type="text/javascript">
							function create_google_account() {
								$('google-account-details').update('<img src="<?php echo ENTRADA_RELATIVE; ?>/images/indicator.gif\" width=\"16\" height=\"16\" alt=\"Please wait\" border=\"0\" style=\"margin-right: 2px; vertical-align: middle\" /> <span class=\"content-small\">Please wait while your account is created ...</span>');
								new Ajax.Updater('google-account-details', '<?php echo ENTRADA_URL; ?>/profile', { method: 'post', parameters: { 'action' : 'google-update', 'google_account' : 1, 'ajax' : 1 }});
							}
							</script>
							<?php
						} else {
							$google_address = html_encode($result["google_id"]."@".$GOOGLE_APPS["domain"]);
							?>
							<span class="input-xlarge uneditable-input"><?php echo $google_address; ?></span>
							<?php
						}
						?>

				</div>
			</div>
			<?php if ($google_address) { ?>
			<div class="control-group">
				<label class="control-label"></label>
				<div class="controls">
					<a href="#reset-google-password-box" id="reset-google-password" class="btn" data-toggle="modal">Reset my <strong><?php echo $GOOGLE_APPS["domain"]; ?></strong> password</a> <a href="http://webmail.<?php echo $GOOGLE_APPS["domain"]; ?>" class="btn" target="_blank">visit <?php echo html_encode($GOOGLE_APPS["domain"]); ?> webmail</a>
				</div>
			</div>
			<?php } ?>
			<div class="control-group"></div>
			<?php } ?>
			<div class="control-group">
				<label class="control-label" for="telephone">Telephone Number:</label>
				<div class="controls">
					<input class="input-xlarge" name="telephone" id="telephone" type="text" placeholder="Example: 613-533-6000 x74918" value="<?php echo html_encode($result["telephone"]); ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="fax">Fax Number:</label>
				<div class="controls">
					<input class="input-xlarge" name="fax" id="fax" type="text" placeholder="Example: 613-533-3204" value="<?php echo html_encode($result["fax"]); ?>" />
				</div>
			</div>
			<div class="control-group"></div>
			<div class="control-group">
				<label class="control-label" for="country_id">Country:</label>
				<div class="controls">
					<?php
						$countries = fetch_countries();
						if ((is_array($countries)) && (count($countries))) {

							$country_id = ($PROCESSED["country_id"])?$PROCESSED["country_id"]:$result["country_id"];

							echo "<select id=\"country_id\" name=\"country_id\" style=\"width: 256px\" onchange=\"provStateFunction(this.value);\">\n";
							echo "<option value=\"0\"".((!country_id) ? " selected=\"selected\"" : "").">-- Select Country --</option>\n";
							foreach ($countries as $country) {
								echo "<option value=\"".(int) $country["countries_id"]."\"".(($country_id == $country["countries_id"]) ? " selected=\"selected\"" : "").">".html_encode($country["country"])."</option>\n";
							}
							echo "</select>\n";
						} else {
							echo "<input type=\"hidden\" id=\"country_id\" name=\"country_id\" value=\"0\" />\n";
							echo "Country information not currently available.\n";
						}
					?>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="prov_state">Province:</label>
				<div class="controls">
					<div id="prov_state_div" class="padding5v">Please select a <strong>Country</strong> from above first.</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="city">City:</label>
				<div class="controls">
					<input class="input-xlarge" name="city" id="city" type="text" value="<?php echo html_encode($result["city"]); ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="address">Address:</label>
				<div class="controls">
					<input class="input-xlarge" name="address" id="address" type="text" value="<?php echo html_encode($result["address"]); ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="postcode">Postal Code:</label>
				<div class="controls">
					<input class="input-xlarge" name="postcode" id="postcode" type="text" placeholder="Example: K7L 3N6" value="<?php echo html_encode($result["postcode"]); ?>" />
				</div>
			</div>
			<?php if ($ENTRADA_USER->getGroup() != "student") { ?>
			<div class="control-group">
				<label class="control-label" for="hours">Office Hours:</label>
				<div class="controls">
					<textarea id="office_hours" name="office_hours" class="expandable input-xlarge" maxlength="100"><?php echo html_encode($result["office_hours"]); ?></textarea>
				</div>
			</div>
			<?php } ?>

			<?php
			load_rte();
			if ($custom_fields) {
				echo "<h2>Department Specific Information</h2>";
				add_notice("The information below has been requested by departments the user is a member of. This information is considered public and may be published on department websites.");
				echo display_notice();
				echo "<div class=\"tabbable departments\">";
				echo "<ul class=\"nav nav-tabs\">";
				$i = 0;
				if (isset($departments)) {
					foreach ($departments as $department_id => $department) {
						if (count($custom_fields[$department_id]) >= 1) {
							?>
							<li class="<?php echo $i == 0 ? "active" : ""; ?>"><a data-toggle="tab" href="#dep-<?php echo $department_id; ?>"><?php echo strlen($department) > 15 ? substr($department, 0, 15)."..." : $department; ?></a></li>
							<?php
							$i++;
						}
					}
				}
				echo "</ul>";

				echo "<div class=\"tab-content\">";
				$i = 0;
				foreach ($departments as $department_id => $department) {
					if (count($custom_fields[$department_id]) >= 1) {
						echo "<div class=\"tab-pane ".($i == 0 ? "active" : "")."\" id=\"dep-".$department_id."\">";
						echo "<h4>".$department."</h4>";
						foreach ($custom_fields[$department_id] as $field) { ?>
							<div class="control-group">
								<label class="control-label <?php echo $field["required"] == "1" ? " form-required" : ""; ?>" for="<?php echo $field["name"]; ?>"><?php echo $field["title"]; ?></label>
								<div class="controls">
									<?php
										$field["type"] = strtolower($field["type"]);
										switch ($field["type"]) {
											case "textarea" :
												?>
												<textarea id="<?php echo $field["name"]; ?>" class="input-xlarge expandable expanded" name="custom[<?php echo $department_id; ?>][<?php echo $field["id"]; ?>]" maxlength="<?php echo $field["length"]; ?>"><?php echo $field["value"]; ?></textarea>
												<?php
											break;
											case "textinput" :
											case "twitter" :
											case "link" :
												?>
												<input type="text" id="<?php echo $field["name"]; ?>" name="custom[<?php echo $department_id; ?>][<?php echo $field["id"]; ?>]" maxlength="<?php echo $field["length"]; ?>" value="<?php echo $field["value"]; ?>" />
												<?php
											break;
											case "richtext" :
												?>
												<textarea id="<?php echo $field["name"]; ?>" class="input-xlarge" name="custom[<?php echo $department_id; ?>][<?php echo $field["id"]; ?>]" maxlength="<?php echo $field["length"]; ?>"><?php echo $field["value"]; ?></textarea>
												<?php
											break;
											case "checkbox" :
												?>
												<label class="checkbox"><input type="checkbox" id="<?php echo $field["name"]; ?>" name="custom[<?php echo $department_id; ?>][<?php echo $field["id"]; ?>]" value="<?php echo $field["value"]; ?>" <?php echo $field["value"] == "1" ? " checked=\"checked\"" : ""; ?> />
												<?php echo $field["helptext"] ? $field["helptext"] : ""; ?></label>
												<?php
											break;
										}
									?>

								</div>
							</div>
						<?php }

						$pub_types = array (
							"ar_poster_reports"				=> array("id_field" => "poster_reports_id",				"title" => "title"),
							"ar_peer_reviewed_papers"		=> array("id_field" => "peer_reviewed_papers_id",		"title" => "title"),
							"ar_non_peer_reviewed_papers"	=> array("id_field" => "non_peer_reviewed_papers_id",	"title" => "title"),
							"ar_book_chapter_mono"			=> array("id_field" => "book_chapter_mono_id",			"title" => "title"),
							"ar_conference_papers"			=> array("id_field" => "conference_papers_id",			"title" => "lectures_papers_list")
						);
						if (isset($pub_types)) {
							foreach ($pub_types as $type_table => $data) {
								$query = "	SELECT a.`".$data["id_field"]."` AS `id`, a.`".$data["title"]."` AS `title`, a.`year_reported`, b.`id` AS `dep_pub_id`
											FROM `".$type_table."` AS a
											LEFT JOIN `profile_publications` AS b
											ON a.`proxy_id` = b.`proxy_id`
											AND b.`pub_id` = a.`".$data["id_field"]."`
											AND (b.`dep_id` = ".$db->qstr($department_id). " || b.`dep_id` IS NULL)
											WHERE a.`proxy_id` = ".$db->qstr($ENTRADA_USER->getID());
								$pubs = $db->GetAll($query);
								if ($pubs) {
									echo "<h4>Publications on ".$department." Website</h4>";
									?>
									<h4><?php echo ucwords(str_replace("ar ", "", str_replace("_", " ", $type_table))); ?></h4>
									<table width="100%" cellpadding="0" cellspacing="0" border="0" class="table table-striped table-hover table-bordered table-nowrap">
										<thead>
											<tr>
												<th>Title</th>
												<th width="10%">Date</th>
												<th width="8%">Visible</th>
											</tr>
										</thead>
										<tbody>
										<?php foreach ($pubs as $publication) {
											?>
											<tr data-id="<?php echo $publication["id"]; ?>">
												<td><?php echo $publication["title"]; ?></td>
												<td><?php echo $publication["year_reported"]; ?></td>
												<td><input type="checkbox" name="publications[<?php echo str_replace("ar_", "", $type_table); ?>][<?php echo $department_id; ?>][<?php echo $publication["id"]; ?>]" <?php echo ($publication["dep_pub_id"] != NULL ? "checked=\"checked\"" : ""); ?> /></td>
											</tr>
											<?php
										}
										?>
										</tbody>
									</table>
									<?php
								}
							}
						}

						echo "</div>";
						$i++;
					}
				}
				echo "</div>";
				echo "</div>";
			}
			?>
			<div>
				<div class="pull-right">
					<input type="submit" class="btn btn-primary right" value="Save Profile" />
				</div>
			</div>
		</form>
		</div>
		<div id="upload-image" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="label" aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 id="label">Upload Photo</h3>
			</div>
			<div class="modal-body">
				<div class="preview-img"></div>
				<div class="description alert" style="height:264px;width:483px;padding:20px;">
					To upload a new profile image you can drag and drop it on this area, or use the Browse button to select an image from your computer.
				</div>
			</div>
			<div class="modal-footer">
				<form name="upload_profile_image_form" id="upload_profile_image_form" action="<?php echo ENTRADA_URL; ?>/profile" method="post" enctype="multipart/form-data">
					<input type="hidden" name="coordinates" id="coordinates" value="" />
					<input type="hidden" name="dimensions" id="dimensions" value="" />
					<input type="file" name="image" id="image" />
				</form>
				<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
				<button id="upload-image-button" class="btn btn-primary">Upload</button>
			</div>
		</div>
		<div class="modal hide fade" id="reset-hash-modal">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Private Hash Reset</h3>
			</div>
			<div class="modal-body">
				<div class="alert alert-info">
					<strong>Please note:</strong> You are about to reset your private hash. Please confirm below that you would like to proceed with the reset.
				</div>
			</div>
			<div class="modal-footer">
				<a href="#" class="btn pull-left" data-dismiss="modal">Cancel</a>
				<a href="#" class="btn btn-primary" id="reset-hash">Reset Hash</a>
			</div>
		</div>
		<div class="modal hide fade" id="password-change-modal">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Update Password</h3>
			</div>
			<div class="modal-body">
				<div id="pw-change-msg"></div>
				<form action="" method="POST" class="form-horizontal" id="update-pw-form">
					<div class="control-group">
						<label for="current_password" class="control-label">Current Password:</label>
						<div class="controls">
							<input type="password" name="current_password" id="current_password" placeholder="Please enter your current password." />
						</div>
					</div>
					<div class="control-group">
						<label for="new_password" class="control-label">New Password:</label>
						<div class="controls">
							<input type="password" name="new_password" id="new_password" placeholder="Please enter your new password." />
						</div>
					</div>
					<div class="control-group">
						<label for="new_password_confirm" class="control-label">Confirm Password:</label>
						<div class="controls">
							<input type="password" name="new_password_confirm" id="new_password_confirm" placeholder="Please repeat your new password." />
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<a href="#" class="btn pull-left" data-dismiss="modal">Cancel</a>
				<a href="#" class="btn btn-primary" id="update-pw">Update</a>
			</div>
		</div>
		<?php
		if (((bool) $GOOGLE_APPS["active"]) && $result["google_id"] && !in_array($result["google_id"], array("opt-out", "opt-in"))) {
			?>
			<div id="reset-google-password-box" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-header">
					<h1>Reset <strong><?php echo ucwords($GOOGLE_APPS["domain"]); ?></strong> Password</h1>
				</div>
				<div class="modal-body">
					<div id="reset-google-password-form">
						<div id="reset-google-password-form-status">To reset your <?php echo ucwords($GOOGLE_APPS["domain"]); ?> account password at Google, please enter your new password below and click the <strong>Submit</strong> button.</div>
						<form action="#" method="post">
							<table style="width: 100%; margin-top: 15px" cellspacing="2" cellpadding="0">
								<colgroup>
									<col style="width: 35%" />
									<col style="width: 65%" />
								</colgroup>
								<tbody>
									<tr>
										<td><label for="google_password_1" class="form-required">New Password</label></td>
										<td><input type="password" id="google_password_1" name="password1" value="" style="width: 175px" maxlength="24" /></td>
									</tr>
									<tr>
										<td><label for="google_password_2" class="form-required">Re-Enter Password</label></td>
										<td><input type="password" id="google_password_2" name="password2" value="" style="width: 175px" maxlength="24" /></td>
									</tr>
								</tbody>
							</table>
						</form>
					</div>
					<div id="reset-google-password-waiting" class="display-generic" style="display: none">
						<img src="<?php echo ENTRADA_RELATIVE; ?>/images/indicator.gif" width="16" height="16" alt="Please wait" border="0" style="margin-right: 2px; vertical-align: middle" /> <span class="content-small">Please wait while your password is being changed ...</span>
					</div>
					<div id="reset-google-password-success" class="display-success" style="display: none">
						We have successfully reset your <?php echo $GOOGLE_APPS["domain"]; ?> account password at Google.<br /><br />If you would like to log into your webmail account, please do so via <a href="http://webmail.qmed.ca" target="_blank">http://webmail.qmed.ca</a>.
					</div>
				</div>
				<div class="modal-footer">
					<button id="reset-google-password-close" class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
					<button id="reset-google-password-submit" class="btn btn-primary">Submit</button>
				</div>
			</div>
			<script type="text/javascript" defer="defer">
			$('reset-google-password-close').observe('click', function() {
				$('reset-google-password-submit', 'reset-google-password-form').invoke('show');
				$('reset-google-password-success', 'reset-google-password-waiting').invoke('hide');

				$('google_password_1').setValue('');
				$('google_password_2').setValue('');
				Control.Modal.close();
			});

			$('reset-google-password-submit').observe('click', function() {
				$('reset-google-password-submit', 'reset-google-password-form').invoke('hide');
				$('reset-google-password-waiting').show();

				if ($('google_password_1') && $('google_password_2')) {
					var new_password = $F('google_password_1');
					var test_password = $F('google_password_2');

					if (new_password && test_password) {
						if (new_password == test_password) {
							new Ajax.Request('<?php echo ENTRADA_URL; ?>/profile', {
								method: 'post',
								parameters: {
									'action' : 'google-password-reset',
									'password' : new_password,
									'ajax' : 1
								},
								onSuccess: function(response) {
									$('reset-google-password-form-status').update('');
									$('reset-google-password-waiting').hide();
									$('reset-google-password-success').show();
								},
								onFailure: function(response) {
									$('reset-google-password-form-status').update('<div class="display-error">We were unable to reset your password at this time, please try again later. If this error persists please contact the system administrator and inform them of the error.</div>');
									$('reset-google-password-waiting').hide();
									$('reset-google-password-submit', 'reset-google-password-form').invoke('show');
								}
							});
						} else {
							$('reset-google-password-form-status').update('<div class="display-error">Your passwords did not match, please try again.</div>');
							$('reset-google-password-waiting').hide();
							$('reset-google-password-submit', 'reset-google-password-form').invoke('show');
						}
					} else {
						$('reset-google-password-form-status').update('<div class="display-error" style="margin: 0">Please make sure you enter your new password, then re-enter it again in the space provided.</div>');
						$('reset-google-password-waiting').hide();
						$('reset-google-password-submit', 'reset-google-password-form').invoke('show');
					}
				} else {
					$('reset-google-password-form-status').update('<div class="display-error" style="margin: 0">Please make sure you enter your new password, then re-enter it again in the space provided.</div>');
					$('reset-google-password-waiting').hide();
					$('reset-google-password-submit', 'reset-google-password-form').invoke('show');
				}
			});
			</script>
			<?php
        }
	} else {
		add_notice("Unfortunately your ".APPLICATION_NAME." profile is not accessible at this time, please try again later.");

		echo display_notice();

		application_log("error", "A user profile was not available in the database? Database said: ".$db->ErrorMsg());
	}
}