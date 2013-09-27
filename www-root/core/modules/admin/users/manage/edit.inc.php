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
 * Allows administrators to edit users from the entrada_auth.user_data table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_USERS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("user", "update", false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
    /**
     * This function should not exist, but the way this page is built it sort of has to.
     * @param int $entry_year
     * @param int $grad_year
     */
    function build_entry_grad_year_container($entry_year = "", $grad_year = "") {
        $output  = "<div id=\"entry_year_container\" style=\"float: left; margin-right: 20px;\">\n";
        $output .= "    <label for=\"entry_year\" class=\"form-required\" style=\"display: block;\">Year of Program Entry</label>&nbsp;\n";
        $output .= "    <select id=\"entry_year\" name=\"entry_year\" style=\"width: 140px\">\n";

        $selected_year = ($entry_year) ? $entry_year : (date("Y", time()) - ((date("m", time()) < 7) ?  1 : 0));
        for($i = fetch_first_year(); $i >= 1995; $i--) {
            $selected = $selected_year == $i;
            $output .= build_option($i, $i, $selected);
        }
        $output .= "    </select>\n";
        $output .= "</div>\n";
        $output .= "&nbsp;&nbsp;\n";
        $output .= "<div id=\"grad_year_container\" style=\"float: left;\">\n";
        $output .= "    <label for=\"grad_year\" class=\"form-required\" style=\"display: block;\">Expected Graduation Year</label>&nbsp;\n";
        $output .= "    <select id=\"grad_year\" name=\"grad_year\" style=\"width: 140px;\">\n";

        for($i = (fetch_first_year() + 1); $i >= 1995; $i--) {
            $selected = ($grad_year == $i);
            $output .= build_option($i, $i, $selected);
        }

        $output .= "    </select>\n";
        $output .= " </div>\n";

        return $output;
    }

	if ($PROXY_ID) {
		$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($PROXY_ID);
		$user_record = $db->GetRow($query);
		if ($user_record) {

			$BREADCRUMB[] = array("url" => "", "title" => "Edit Profile");

			$PROCESSED_ACCESS = array();
			$PROCESSED_DEPARTMENTS = array();

			$custom_fields = fetch_department_fields($PROXY_ID);

			/*
			 * Get the user departments and the custom fields for the departments.
			 */
			$user_departments = get_user_departments($PROXY_ID);
			foreach ($user_departments as $department) {
			   $PROCESSED_DEPARTMENTS[$department["department_id"]] = $department["department_title"];
			}
			ksort($PROCESSED_DEPARTMENTS);

			// Error Checking
			switch ($STEP) {
				case 2 :

					if (isset($_POST["custom"]) && $_POST["custom"]) {
						/*
						* Fetch the custom fields
						*/
						$query = "SELECT * FROM `profile_custom_fields` WHERE `organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())." AND `active` = '1' ORDER BY `organisation_id`, `department_id`, `id`";
						$dep_fields = $db->GetAssoc($query);
						if ($dep_fields) {
							foreach ($dep_fields as $field_id => $field) {
								switch (strtolower($field["type"])) {
									case "checkbox" :
										if (isset($_POST["custom"][$field["department_id"]][$field_id])) {
											$PROCESSED["custom"][$field_id] = "1";
										} else {
											$PROCESSED["custom"][$field_id] = "0";
										}
									break;
									default :
										if ($_POST["custom"][$field["department_id"]][$field_id]) {
											if ($field["length"] != NULL && strlen($_POST["custom"][$field["department_id"]][$field_id]) > $field["length"]) {
												add_error("<strong>".$field["title"]."</strong> has a character limit of <strong>".$field["length"]."</strong> and you have entered <strong>".strlen($_POST["custom"][$field["department_id"]][$field_id])."</strong> characters. Please edit your response and re-save your profile.");
											} else {
												$PROCESSED["custom"][$field_id] = clean_input($_POST["custom"][$field["department_id"]][$field_id], array("trim", strtolower($field["type"]) == "richtext" ? "html" : (strtolower($field["type"]) == "twitter" ? "alphanumeric" : "striptags")));
											}
										} else {
											if ($field["required"] == "1") {
												add_error("<strong>".$field["title"]."</strong> is a required field, please enter a response and re-save your profile.");
											}
										}
									break;
								}
							}
						}
					}

					if (isset($_POST["publications"]) && $_POST["publications"]) {
						foreach ($_POST["publications"] as $pub_type => $ppublications) {
							foreach ($ppublications as $department_id => $publications) {
								foreach ($publications as $publication_id => $status) {
									$PROCESSED["publications"][$pub_type][$department_id][] = clean_input($publication_id, "numeric");
								}
							}
						}
					}

					$permissions = json_decode($_POST["permissions"], true);
					$permissions = json_decode($permissions["acl"], true);

					if (isset($permissions[0]["org_id"]) && $default_organisation_id = clean_input($permissions[0]["org_id"], array("trim", "int"))) {
						$PROCESSED["organisation_id"] = $default_organisation_id;
					} else {
						$ERROR++;
						$ERRORSTR[] = "At least one <strong>Organisation</strong> is required.";
					}
					/**
					 * Non-required (although highly recommended) field for staff / student number.
					 */
					if ((isset($_POST["number"])) && ($number = clean_input($_POST["number"], array("trim", "int")))) {
						if ($number != $user_record["number"]) {
							$query	= "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `number` = ".$db->qstr($number);
							$result	= $db->GetRow($query);
							if ($result) {
								$ERROR++;
								$ERRORSTR[] = "You are attempting to update the staff / student number; however, the new number already exists in the database under ".html_encode($result["firstname"]." ".$result["lastname"]).".";
							} else {
								$PROCESSED["number"] = $number;
							}
						}
					} else {
						$NOTICE++;
						$NOTICESTR[] = "There was no faculty, staff or student number attached to this profile. If this user is a affiliated with the University, please make sure you add this information.";

						$PROCESSED["number"] = 0;
					}

					/**
					 * Required field "username" / Username.
					 */
					if ((isset($_POST["username"])) && ($username = clean_input($_POST["username"], "credentials"))) {
						if ($username != $user_record["username"]) {
							$query	= "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `username` = ".$db->qstr($username);
							$result	= $db->GetRow($query);
							if ($result) {
								$ERROR++;
								$ERRORSTR[] = "You are attempting to update the username; however, this username already exists in the database under ".html_encode($result["firstname"]." ".$result["lastname"]).".";
							} else {
								if ((strlen($username) >= 3) && (strlen($username) <= 24)) {
									$PROCESSED["username"] = $username;
								} else {
									$ERROR++;
									$ERRORSTR[] = "The new username must be between 3 and 24 characters.";
								}
							}
						} else {
							$PROCESSED["username"] = $user_record["username"];
						}
					} else {
						$PROCESSED["username"] = $user_record["username"];
						$ERROR++;
						$ERRORSTR[] = "You must provide a valid username for this user to login with. We suggest that you use their University NetID if at all possible.";
					}

					/**
					 * Non-Required field "password" / Password.
					 * This is not required in the edit screen because the password is only changed
					 * if there is an entry made here.
					 */
					if ((isset($_POST["password"])) && ($password = clean_input($_POST["password"], "trim"))) {
						if ((strlen($password) >= 6) && (strlen($password) <= 48)) {
                            $PROCESSED["salt"] = hash("sha256", (uniqid(rand(), 1) . time() . $PROXY_ID));
							$PROCESSED["password"] = sha1($password.$PROCESSED["salt"]);
						} else {
                            add_error("The new password must be between 6 and 48 characters in length.");
						}
					}

					/*
					 * Non-Required field "clinical" / Clinical.
					 */
					if (!isset($_POST["clinical"])) {
						$PROCESSED["clinical"] = 0;
					} else {
						$PROCESSED["clinical"] = 1;
					}

					/*
					 * Required field "account_active" / Account Status.
					 */
					if ((isset($_POST["account_active"])) && ($_POST["account_active"] == "true")) {
						$PROCESSED_ACCESS["account_active"] = "true";
					} else {
						$PROCESSED_ACCESS["account_active"] = "false";
					}

					/**
					 * Required field "access_starts" / Access Start (validated through validate_calendars function).
					 * Non-required field "access_finish" / Access Finish (validated through validate_calendars function).
					 */
					$access_date = validate_calendars("access", true, false);
					if ((isset($access_date["start"])) && ((int) $access_date["start"])) {
						$PROCESSED_ACCESS["access_starts"] = (int) $access_date["start"];
					}

					if ((isset($access_date["finish"])) && ((int) $access_date["finish"])) {
						$PROCESSED_ACCESS["access_expires"] = (int) $access_date["finish"];
					} else {
						$PROCESSED_ACCESS["access_expires"] = 0;
					}

					/**
					 * Non-required field "prefix" / Prefix.
					 */
					if ((isset($_POST["prefix"])) && (@in_array($prefix = clean_input($_POST["prefix"], "trim"), $PROFILE_NAME_PREFIX))) {
						$PROCESSED["prefix"] = $prefix;
					} else {
						$PROCESSED["prefix"] = "";
					}

					/**
					 * Required field "firstname" / Firstname.
					 */
					if ((isset($_POST["firstname"])) && ($firstname = clean_input($_POST["firstname"], "trim"))) {
						$PROCESSED["firstname"] = $firstname;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The firstname of the user is a required field.";
					}

					/**
					 * Required field "lastname" / Lastname.
					 */
					if ((isset($_POST["lastname"])) && ($lastname = clean_input($_POST["lastname"], "trim"))) {
						$PROCESSED["lastname"] = $lastname;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The lastname of the user is a required field.";
					}

					/**
					 * Non-Required field "gender" / Gender.
					 */
					if (isset($_POST["gender"]) && in_array((int) $_POST["gender"], array(1, 2))) {
						$PROCESSED["gender"] = (int) $_POST["gender"];
					}

					if (!isset($PROCESSED["gender"])) {
						$PROCESSED["gender"] = 0;
					}

					/**
					 * Required field "email" / Primary E-Mail.
					 */
					if ((isset($_POST["email"])) && ($email = clean_input($_POST["email"], "trim", "lower"))) {
						if (@valid_address($email)) {
							$PROCESSED["email"] = $email;
						} else {
							$ERROR++;
							$ERRORSTR[] = "The primary e-mail address you have provided is invalid. Please make sure that you provide a properly formatted e-mail address.";
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "The primary e-mail address is a required field.";
					}

					/**
					 * Non-required field "office_hours" / Office Hours.
					 */
					if ((isset($_POST["office_hours"])) && ($office_hours = clean_input($_POST["office_hours"], array("notags","encode", "trim")))) {
						$PROCESSED["office_hours"] = ((strlen($office_hours) > 100) ? substr($office_hours, 0, 97)."..." : $office_hours);
					} else {
						$PROCESSED["office_hours"] = "";
					}

					/**
					 * Non-required field "email_alt" / Alternative E-Mail.
					 */
					if ((isset($_POST["email_alt"])) && ($email_alt = clean_input($_POST["email_alt"], "trim", "lower"))) {
						if (@valid_address($email_alt)) {
							$PROCESSED["email_alt"] = $email_alt;
						} else {
							$ERROR++;
							$ERRORSTR[] = "The alternative e-mail address you have provided is invalid. Please make sure that you provide a properly formatted e-mail address or leave this field empty if you do not wish to display one.";
						}
					} else {
						$PROCESSED["email_alt"] = "";
					}

					/**
					 * Non-required field "telephone" / Telephone Number.
					 */
					if ((isset($_POST["telephone"])) && ($telephone = clean_input($_POST["telephone"], "trim")) && (strlen($telephone) >= 10) && (strlen($telephone) <= 25)) {
						$PROCESSED["telephone"] = $telephone;
					} else {
						$PROCESSED["telephone"] = "";
					}

					/**
					 * Non-required field "fax" / Fax Number.
					 */
					if ((isset($_POST["fax"])) && ($fax = clean_input($_POST["fax"], "trim")) && (strlen($fax) >= 10) && (strlen($fax) <= 25)) {
						$PROCESSED["fax"] = $fax;
					} else {
						$PROCESSED["fax"] = "";
					}

					/**
					 * Non-required field "address" / Address.
					 */
					if ((isset($_POST["address"])) && ($address = clean_input($_POST["address"], array("trim", "ucwords"))) && (strlen($address) >= 6) && (strlen($address) <= 255)) {
						$PROCESSED["address"] = $address;
					} else {
						$PROCESSED["address"] = "";
					}

					/**
					 * Non-required field "city" / City.
					 */
					if ((isset($_POST["city"])) && ($city = clean_input($_POST["city"], array("trim", "ucwords"))) && (strlen($city) >= 3) && (strlen($city) <= 35)) {
						$PROCESSED["city"] = $city;
					} else {
						$PROCESSED["city"] = "";
					}

					if ((isset($_POST["country_id"])) && ($tmp_input = clean_input($_POST["country_id"], "int"))) {
						$query = "SELECT * FROM `global_lu_countries` WHERE `countries_id` = ".$db->qstr($tmp_input);
						$result = $db->GetRow($query);
						if ($result) {
							$PROCESSED["country_id"] = $tmp_input;
						} else {
							$ERROR++;
							$ERRORSTR[] = "The selected country does not exist in our countries database. Please select a valid country.";

							application_log("error", "Unknown countries_id [".$tmp_input."] was selected. Database said: ".$db->ErrorMsg());
						}
					} else {
						$ERROR++;
						$ERRORSTR[]	= "You must select a country.";
					}

					if ((isset($_POST["prov_state"])) && ($tmp_input = clean_input($_POST["prov_state"], array("trim", "notags")))) {
						$PROCESSED["province_id"] = 0;
						$PROCESSED["province"] = "";

						if (ctype_digit($tmp_input) && ($tmp_input = (int) $tmp_input)) {
							if ($PROCESSED["country_id"]) {
								$query = "SELECT * FROM `global_lu_provinces` WHERE `province_id` = ".$db->qstr($tmp_input)." AND `country_id` = ".$db->qstr($PROCESSED["country_id"]);
								$result = $db->GetRow($query);
								if (!$result) {
									$ERROR++;
									$ERRORSTR[] = "The province / state you have selected does not appear to exist in our database. Please selected a valid province / state.";
								}
							}

							$PROCESSED["province_id"] = $tmp_input;
						} else {
							$PROCESSED["province"] = $tmp_input;
						}

						$PROCESSED["prov_state"] = ($PROCESSED["province_id"] ? $PROCESSED["province_id"] : ($PROCESSED["province"] ? $PROCESSED["province"] : ""));
					}

					/**
					 * Non-required field "postcode" / Postal Code.
					 */
					if ((isset($_POST["postcode"])) && ($postcode = clean_input($_POST["postcode"], array("trim", "uppercase"))) && (strlen($postcode) >= 5) && (strlen($postcode) <= 12)) {
						$PROCESSED["postcode"] = $postcode;
					} else {
						$PROCESSED["postcode"] = "";
					}

					/**
					 * Non-required field "notes" / General Comments.
					 */
					if ((isset($_POST["notes"])) && ($notes = clean_input($_POST["notes"], array("trim", "notags")))) {
						$PROCESSED["notes"] = $notes;
					} else {
						$PROCESSED["notes"] = "";
					}

					if (!$ERROR && $ENTRADA_ACL->amIAllowed(new UserResource(null, $PROCESSED["organisation_id"]), "update")) {
						$PROCESSED["email_updated"] = time();
						if ($db->AutoExecute(AUTH_DATABASE.".user_data", $PROCESSED, "UPDATE", "id = ".$db->qstr($PROXY_ID))) {
							$query = "SELECT * FROM " . AUTH_DATABASE . ".`user_access`
									  WHERE `user_id` = " . $db->qstr($PROXY_ID) . "
									  AND `app_id` = " . $db->qstr(AUTH_APP_ID);

							$results = $db->GetAll($query);
							if ($results) {
								foreach($results as $result) {
									$private_hashes[$result["app_id"]][$result["organisation_id"]][$result["group"]][$result["role"]] = $result["private_hash"];
								}
							}

							$query = "DELETE FROM `".AUTH_DATABASE."`.`user_access`
									  WHERE `user_id` = ".$db->qstr($PROXY_ID) . "
									  AND `app_id` = " . $db->qstr(AUTH_APP_ID);
							if ($db->Execute($query)) {
								if (is_array($permissions)){
									$index = 0;
									foreach ($permissions as $perm) {
										if (!$perm["org_id"]) {
											$ERROR++;
											$ERRORSTR[] = "Please assign an organisation for all permissions.";
										} elseif (!$perm["group_id"]) {
											$ERROR++;
											$ERRORSTR[] = "Please assign a group for all permissions.";
										} elseif (!$perm["role_id"]) {
											$ERROR++;
											$ERRORSTR[] = "Please assign a role for all permissions.";
										} else {
											$PROCESSED_ACCESS["id"] = $perm["access_id"];
											$PROCESSED_ACCESS["user_id"] = $PROXY_ID;
											$PROCESSED_ACCESS["app_id"] = AUTH_APP_ID;
											$PROCESSED_ACCESS["organisation_id"] = $perm["org_id"];

											$query = "SELECT g.`group_name`, r.`role_name`
														FROM `" . AUTH_DATABASE . "`.`system_groups` g, `" . AUTH_DATABASE . "`.`system_roles` r,
															`" . AUTH_DATABASE . "`.`system_group_organisation` gho, `" . AUTH_DATABASE . "`.`organisations` o
														WHERE gho.`groups_id` = " . $perm["group_id"] . " AND g.`id` = " . $perm["group_id"] . " AND
														r.`id` = " . $perm["role_id"] . " AND o.`organisation_id` = " . $perm["org_id"];
											$group_role = $db->GetRow($query);
											$PROCESSED_ACCESS["group"] = $group_role["group_name"];
											$PROCESSED_ACCESS["role"] = $group_role["role_name"];

											$result = $private_hashes[AUTH_APP_ID][$perm["org_id"]][$PROCESSED_ACCESS["group"]][$PROCESSED_ACCESS["role"]];

											if ($result) {
												$PROCESSED_ACCESS["private_hash"] = $result;
											} else {
												$PROCESSED_ACCESS["private_hash"] = generate_hash(32);
											}

											if ($PROCESSED_ACCESS["group"] == "student") {
												if (isset($perm["entry_year"]) && isset($perm["grad_year"])) {
													$entry_year = clean_input($perm["entry_year"],"int");
													$grad_year = clean_input($perm["grad_year"],"int");
													$sanity_start = 1995;
													$sanity_end = (fetch_first_year() + 1);
													if ($grad_year <= $sanity_end && $grad_year >= $sanity_start) {
														$PROCESSED["grad_year"] = $grad_year;
													} else {
														$ERROR++;
														$ERRORSTR[] = "You must provide a valid graduation year.";
													}
													if ($entry_year <= $sanity_end && $entry_year >= $sanity_start) {
														$PROCESSED["entry_year"] = $entry_year;
													} else {
														$ERROR++;
														$ERRORSTR[] = "You must provide a valid program entry year.";
													}
													if (!$ERROR) {
														$query = "	UPDATE `" . AUTH_DATABASE . "`.`user_data`
																	SET `grad_year` = " . $PROCESSED["grad_year"] . ",
																	`entry_year` = " . $PROCESSED["entry_year"] . "
																	WHERE `id` = " . $PROCESSED_ACCESS["user_id"] . "
																	LIMIT 1";
														if (!$db->Execute($query)) {
															$ERROR++;
															$ERRORSTR[] = "Failed to set the entry and grad year." . $query . " DB said: " . $db->ErrorMsg();
														}
													}
												}
											}

											if ($PROCESSED_ACCESS["group"] == "faculty") {
												if (isset($perm["clinical"])) {
													$PROCESSED["clinical"] = clean_input($perm["clinical"], array("trim", "int"));
													$query = "	UPDATE `" . AUTH_DATABASE . "`.`user_data`
																SET `clinical` = " . $PROCESSED["clinical"] . "
																WHERE `id` = " . $PROCESSED_ACCESS["user_id"] . "
																LIMIT 1";
													if (!$db->Execute($query)) {
														$ERROR++;
														$ERRORSTR[] = "Failed to set the clinical field." . $query . " DB said: " . $db->ErrorMsg();
													}
												}
											}

											if (!$ERROR) {
												if ($db->AutoExecute(AUTH_DATABASE.".user_access", $PROCESSED_ACCESS, "INSERT")) {
													if (($PROCESSED_ACCESS["group"] == "medtech") || ($PROCESSED_ACCESS["role"] == "admin")) {
														application_log("error", "USER NOTICE: A new user (".$PROCESSED["firstname"]." ".$PROCESSED["lastname"].") was added to ".APPLICATION_NAME." as ".$PROCESSED_ACCESS["group"]." > ".$PROCESSED_ACCESS["role"].".");
													}
												} else {
													application_log("error", "Unable to insert proxy_id [".$PROCESSED_ACCESS["user_id"]."] into the user_access table. Database said: ".$db->ErrorMsg());
												}
											} else {
												echo display_error();
											}
											$index++;
										} //end else error checking
									} //end for each org_id
								} //end if is_array
							} //end if delete user_access records

							/**
							 * This section of code handles updating the users departmental data.
							 */
							$query = "DELETE FROM `".AUTH_DATABASE."`.`user_departments` WHERE `user_id` = ".$db->qstr($PROXY_ID);
							if ($db->Execute($query)) {
								if (isset($_POST["my_departments"]) && $in_departments = json_decode($_POST["my_departments"], true)) {
									$in_departments = json_decode($in_departments["dept_list"], true);
									if (is_array($in_departments)) {
										foreach ($in_departments as $dept) {
											$department_id = clean_input($dept["department_id"], "int");
											if ($department_id) {
												$PROCESSED_DEPARTMENTS[] = $department_id;
											}
										}
									}
								}

								if(count($PROCESSED_DEPARTMENTS)) {
									foreach ($PROCESSED_DEPARTMENTS as $department_id) {
										if (!$db->AutoExecute(AUTH_DATABASE.".user_departments", array("user_id" => $PROXY_ID, "dep_id" => $department_id), "INSERT")) {
											application_log("error", "Unable to insert proxy_id [".$PROCESSED_ACCESS["user_id"]."] into department [".$department_id."]. Database said: ".$db->ErrorMsg());
										}
									}
								}
							}

                            /**
                             * Remove the user from any cohorts they may reside in.
                             */
                            $cohort_ids = array();

                            $query = "  SELECT a.`group_id`
                                        FROM `groups` AS a
                                        JOIN `group_organisations` AS b
                                        ON a.`group_id` = b.`group_id`
                                        AND b.`organisation_id` = ".$db->qstr($PROCESSED_ACCESS["organisation_id"])."
                                        WHERE `group_type` = 'cohort'
                                        AND `group_active` = 1";
                            $cohorts = $db->GetAll($query);
                            if ($cohorts) {
                                foreach ($cohorts as $cohort) {
                                    $cohort_ids[] = $cohort["group_id"];
                                }
                            }

                            $query = "DELETE FROM `group_members` WHERE `proxy_id` = ".$db->qstr($PROXY_ID)." AND `group_id` IN (".implode(",", $cohort_ids).")";
                            $db->Execute($query);

                            /**
                             * If this is a student, try and find a cohort to add them to.
                             */
                            if ($PROCESSED_ACCESS["group"] == "student") {
                                $query = "  SELECT a.`group_id`
                                            FROM `groups` AS a
                                            JOIN `group_organisations` AS b
                                            ON a.`group_id` = b.`group_id`
                                            AND b.`organisation_id` = ".$db->qstr($PROCESSED_ACCESS["organisation_id"])."
                                            WHERE a.`group_name` = 'Class of ".$PROCESSED_ACCESS["role"]."'
                                            AND a.`group_type` = 'cohort'
                                            AND a.`group_active` = 1";
                                $group_id = $db->GetOne($query);
                                if ($group_id) {
                                    $gmember = array (
                                        'group_id' => $group_id,
                                        'proxy_id' => $PROXY_ID,
                                        'start_date' => 0,
                                        'finish_date' => 0,
                                        'member_active' => 1,
                                        'entrada_only' => 1,
                                        'updated_date' => time(),
                                        'updated_by' => $ENTRADA_USER->getID()
                                    );
                                    $db->AutoExecute("group_members", $gmember, "INSERT");
                                }
                            }

							if (isset($PROCESSED["custom"])) {
								foreach ($PROCESSED["custom"] as $field_id => $value) {
									$query = "DELETE FROM `profile_custom_responses` WHERE `field_id` = ".$db->qstr($field_id)." AND `proxy_id` = ".$db->qstr($PROXY_ID);
									$db->Execute($query);

									$query = "INSERT INTO `profile_custom_responses` (`field_id`, `proxy_id`, `value`) VALUES (".$db->qstr($field_id).", ".$db->qstr($PROXY_ID).", ".$db->qstr($value).")";
									$db->Execute($query);
								}
							}

							if (isset($PROCESSED["publications"])) {
								$query = "DELETE FROM `profile_publications` WHERE `proxy_id` = ".$db->qstr($ENTRADA_USER->getID());
								if ($db->Execute($query)) {
									foreach ($PROCESSED["publications"] as $pub_type => $ppublications) {
										foreach ($ppublications as $dep_id => $publications) {
											foreach ($publications as $publication) {
												$query = "INSERT INTO `profile_publications` (`pub_type`, `pub_id`, `dep_id`, `proxy_id`) VALUES (".$db->qstr($pub_type).", ".$db->qstr($publication).", ".$db->qstr($dep_id).", ".$db->qstr($ENTRADA_USER->getID()).")";
												$db->Execute($query);
											}
										}
									}
								}
							}

							$url = ENTRADA_URL."/admin/users/manage?id=".$PROXY_ID;

							$SUCCESS++;
							$SUCCESSSTR[] = "You have successfully updated the <strong>".html_encode($PROCESSED["firstname"]." ".$PROCESSED["lastname"])."</strong> account in the authentication system.<br /><br />You will now be redirected to the users profile page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";

							header( "refresh:5;url=".$url );

							application_log("success", "Proxy ID [".$ENTRADA_USER->getID()."] successfully updated the proxy id [".$PROXY_ID."] user profile.");
						} else {
							$ERROR++;
							$ERRORSTR[] = "Unable to update this user account at this time. The system administrator has been informed of this error, please try again later.";

							application_log("error", "Unable to update user account [".$PROXY_ID."]. Database said: ".$db->ErrorMsg());
						}
					}

					if ($ERROR) {
						$STEP = 1;
					}
				break;
				case 1 :
				default :
					$PROCESSED = $user_record;

					$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_access` WHERE `user_id` = ".$db->qstr($PROXY_ID)." AND `app_id` = ".$db->qstr(AUTH_APP_ID);
					$PROCESSED_ACCESS = $db->GetRow($query);

					//Initialize Organisation ID array for initial page display
					$organisation_ids = array();
					$query = "SELECT `organisation_id` FROM `".AUTH_DATABASE."`.`user_access` WHERE `user_id` = ".$db->qstr($PROXY_ID);
					$results = $db->GetAll($query);
					foreach ($results as $result) {
						$organisation_ids[] = $result["organisation_id"];
					}
					//ensure the user's default org is added
					if (!in_array($user_record["organisation_id"], $organisation_ids)) {
						$organisation_ids[] = $user_record["organisation_id"];
					}

					$query = "SELECT LOWER(ua.`organisation_id`) as `organisation_id`, lower(ua.`group`) as `group`, lower(ua.`role`) as `role`
							  FROM `" . AUTH_DATABASE . "`.`user_access` ua
							  WHERE ua.`user_id` = " . $PROXY_ID;
					$my_orgs_groups_roles = $db->GetAll($query);
				break;
			}

			// Display Page.
			switch ($STEP) {
				case 2 :
					if ($NOTICE) {
						echo display_notice();
					}

					if ($SUCCESS) {
						$query = "SELECT *
								  FROM `" . AUTH_DATABASE . "`.`user_access` a
								  WHERE a.`user_id` = " . $db->qstr($ENTRADA_USER->getID()) . "
								  AND a.`organisation_id` = " . $db->qstr($_SESSION["tmp"]["current_org"]) . "
								  AND a.`group` = " . $db->qstr($_SESSION["tmp"]["current_group"]) . "
								  AND a.`role` = " . $db->qstr($_SESSION["tmp"]["current_role"]);

						$result = $db->getRow($query);
						if ($result) {
							$ENTRADA_USER->setAccessId($result["id"]);
							$_SESSION["permissions"] = permissions_load();
						}

						unset($ENTRADA_ACL);
						$ENTRADA_ACL = new Entrada_Acl($_SESSION["details"]);
						$ENTRADA_CACHE->remove("acl_".$ENTRADA_USER->getID());
						$ENTRADA_CACHE->save($ENTRADA_ACL, "acl_".$ENTRADA_USER->getID());
						echo display_success();
					}
				break;
				case 1 :
				default :
					$query = "SELECT *
							  FROM `" . AUTH_DATABASE . "`.`user_access` a
							  WHERE a.`user_id` = " . $db->qstr($ENTRADA_USER->getID()) . "
							  AND a.`id` = " . $db->qstr($ENTRADA_USER->getAccessId());
					$result = $db->getRow($query);
					if ($result) {
						$current_org = $result["organisation_id"];
						$current_group = $result["group"];
						$current_role = $result["role"];
					}
					$_SESSION["tmp"]["current_org"] = $current_org;
					$_SESSION["tmp"]["current_group"] = $current_group;
					$_SESSION["tmp"]["current_role"] = $current_role;


//					$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/tabpane/tabpane.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
					$HEAD[] = "<link href=\"".ENTRADA_URL."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
					$HEAD[] = "<style type=\"text/css\"> .dynamic-tab-pane-control .tab-page {height:auto;}</style>\n";
					$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/selectchained.js\"></script>\n";
					$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/picklist.js\"></script>\n";
					$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";

					$i = count($HEAD);
					$HEAD[$i]  = "<script type=\"text/javascript\">\n";
					$HEAD[$i] .= "addListGroup('account_type', 'cs-top');\n";
					if (is_array($SYSTEM_GROUPS)) {
						$item = 1;
						foreach ($SYSTEM_GROUPS as $group => $roles) {
							$HEAD[$i] .= "addList('cs-top', '".ucwords($group)."', '".$group."', 'cs-sub-".$item."', ".(((isset($PROCESSED_ACCESS["group"])) && ($PROCESSED_ACCESS["group"] == $group)) ? "1" : "0").");\n";
							if (is_array($roles)) {
								foreach ($roles as $role) {
									$HEAD[$i] .= "addOption('cs-sub-".$item."', '".ucwords($role)."', '".$role."', ".(((isset($PROCESSED_ACCESS["role"])) && ($PROCESSED_ACCESS["role"] == $role)) ? "1" : "0").");\n";
								}
							}
							$item++;
						}
					}
					$HEAD[$i] .= "</script>\n";

					$ONLOAD[] = "setMaxLength()";

					$DEPARTMENT_LIST = array();
					$query = "	SELECT a.`department_id`, a.`department_title`, a.`organisation_id`, b.`entity_title`
								FROM `".AUTH_DATABASE."`.`departments` AS a
								LEFT JOIN `".AUTH_DATABASE."`.`entity_type` AS b
								ON a.`entity_id` = b.`entity_id`
								ORDER BY a.`department_title`";
					$results = $db->GetAll($query);
					if ($results) {
						foreach ($results as $key => $result) {
							$DEPARTMENT_LIST[$result["organisation_id"]][] = array("department_id"=>$result['department_id'], "department_title" => $result["department_title"], "entity_title" => $result["entity_title"]);
						}
					}

					$ONLOAD[] = "toggle_visibility_checkbox($('send_notification'), 'send_notification_msg')";

					display_status_messages();

					$ONLOAD[] = "provStateFunction('".$PROCESSED["country_id"]."', '".$PROCESSED["province_id"]."')";



					?>
					<h1>Edit Profile for <strong><?php echo html_encode($user_record["firstname"]." ".$user_record["lastname"]); ?></strong></h1>
					<form id="user-edit" class="form-horizontal" action="<?php echo ENTRADA_URL; ?>/admin/users/manage?section=edit&id=<?php echo $PROXY_ID; ?>&amp;step=2" method="post">
						<h2>Account Details</h2>
						<div class="control-group">
                            <label class="form-nrequired control-label" for="number">Staff / Student Number:</label>
							<div class="controls">
								<input type="text" id="number" name="number" value="<?php echo ((isset($PROCESSED["number"])) ? html_encode($PROCESSED["number"]) : ""); ?>" maxlength="25" />
								<span class="help-inline"><strong>Important:</strong> Required when ever possible.</span>
							</div>
						</div>
                        <!--- End control-group ---->

						<div class="control-group">
                            <label class="control-label form-required" for="username">Username:</label>
							<div class="controls">
								<input type="text" id="username" name="username" value="<?php echo ((isset($PROCESSED["username"])) ? html_encode($PROCESSED["username"]) : ""); ?>" maxlength="25" />
							</div>
						</div>
                        <!--- End control-group ---->

						<div class="control-group">
							<label class="control-label" for="password">Password:</label>
							<div class="controls">
								<input type="password" id="password" name="password" class="input-medium" value="" maxlength="48" />
								<span class="help-inline"><strong>Important:</strong> This will change the current password.</span>
							</div>
						</div>
                        <!--- End control-group ---->

						<h2>Account Options</h2>
						<div class="control-group">
							<label class="control-label form-required" for="account_active">Account Status:</label>
							<div class="controls">
								<select id="account_active" name="account_active">
									<option value="true"<?php echo (((!isset($PROCESSED_ACCESS["account_active"])) || ($PROCESSED_ACCESS["account_active"] == "true")) ? " selected=\"selected\"" : ""); ?>>Active</option>
									<option value="false"<?php echo (($PROCESSED_ACCESS["account_active"] == "false") ? " selected=\"selected\"" : ""); ?>>Disabled</option>
								</select>
							</div>
						</div>
                        <!--- End control-group ---->

                        <div class="control-group">
							<table>
								<?php echo generate_calendars("access", "Access", true, true, ((isset($PROCESSED_ACCESS["access_starts"])) ? $PROCESSED_ACCESS["access_starts"] : time()), true, false, ((isset($PROCESSED_ACCESS["access_expires"])) ? $PROCESSED_ACCESS["access_expires"] : 0)); ?>
							</table>
						</div>

						<h2>Personal Information</h2>
						<div class="control-group">
							<label class="control-label form-nrequired" for="prefix">Prefix:</label>
							<div class="controls">
								<select id="prefix" name="prefix" class="input-small">
								<option value=""<?php echo ((!isset($result["prefix"])) ? " selected=\"selected\"" : ""); ?>></option>
								<?php
								if ((@is_array($PROFILE_NAME_PREFIX)) && (@count($PROFILE_NAME_PREFIX))) {
									foreach ($PROFILE_NAME_PREFIX as $key => $prefix) {
										echo "<option value=\"".html_encode($prefix)."\"".(((isset($PROCESSED["prefix"])) && ($PROCESSED["prefix"] == $prefix)) ? " selected=\"selected\"" : "").">".html_encode($prefix)."</option>\n";
									}
								}
								?>
								</select>
							</div>
						</div>
                        <!--- End control-group ---->

						<div class="control-group">
							<label class="control-label form-required" for="firstname">Firstname:</label>
							<div class="controls">
								<input type="text" id="firstname" name="firstname" value="<?php echo ((isset($PROCESSED["firstname"])) ? html_encode($PROCESSED["firstname"]) : ""); ?>" maxlength="35" />
							</div>
						</div>
                        <!--- End control-group ---->

						<div class="control-group">
                            <label class="control-label form-required" for="lastname">Lastname:</label>
							<div class="controls">
								<input type="text" id="lastname" name="lastname" value="<?php echo ((isset($PROCESSED["lastname"])) ? html_encode($PROCESSED["lastname"]) : ""); ?>" maxlength="35" />
							</div>
						</div>
                        <!--- End control-group ---->

						<div class="control-group">
                            <label class="control-label form-required" for="gender">Gender:</label>
							<div class="controls">
								<select name="gender" id="gender">
                                    <option value="0"<?php echo (!isset($PROCESSED["gender"]) || $PROCESSED["gender"] == 0 ? " selected=\"selected\"" : ""); ?>>Not Specified</option>
                                    <option value="1"<?php echo (isset($PROCESSED["gender"]) && $PROCESSED["gender"] == 1 ? " selected=\"selected\"" : ""); ?>>Female</option>
                                    <option value="2"<?php echo (isset($PROCESSED["gender"]) && $PROCESSED["gender"] == 2 ? " selected=\"selected\"" : ""); ?>>Male</option>
								</select>
							</div>
						</div>
                        <!--- End control-group ---->

						<div class="control-group">
                            <label for="email" class="control-label form-required">Primary E-Mail:</label>
							<div class="controls">
								<input type="text" id="email" name="email" value="<?php echo ((isset($PROCESSED["email"])) ? html_encode($PROCESSED["email"]) : ""); ?>" maxlength="128" />
								<span class="help-inline"><strong>Important:</strong> Official e-mail accounts only.</span>
							</div>
						</div>
                        <!--- End control-group ---->

						<div class="control-group">
                            <label class="control-label form-nrequired" for="email_alt">Alternative E-Mail:</label>
							<div class="controls">
								<input type="text" id="email_alt" name="email_alt" value="<?php echo ((isset($PROCESSED["email_alt"])) ? html_encode($PROCESSED["email_alt"]) : ""); ?>" maxlength="128" />
							</div>
						</div>
                        <!--- End control-group ---->

						<div class="control-group">
                            <label class="control-label form-nrequired" for="telephone">Telephone Number:</label>
							<div class="controls">
								<input type="text" id="telephone" name="telephone" value="<?php echo ((isset($PROCESSED["telephone"])) ? html_encode($PROCESSED["telephone"]) : ""); ?>" maxlength="25" />
                                <span class="content-small">(<strong>Example:</strong> 613-533-6000 x74918)</span>
							</div>
						</div>
                        <!--- End control-group ---->

						<div class="control-group">
                            <label class="control-label form-nrequired" for="fax">Fax Number:</label>
							<div class="controls">
								<input type="text" id="fax" name="fax" value="<?php echo ((isset($PROCESSED["fax"])) ? html_encode($PROCESSED["fax"]) : ""); ?>" maxlength="25" />
                                <span class="content-small">(<strong>Example:</strong> 613-533-3204)</span>
							</div>
						</div>
                        <!--- End control-group ---->

						<div class="control-group">
                            <label class="control-label form-required" for="country_id">Country:</label>
							<div class="controls">
								<?php
								$countries = fetch_countries();
								if ((is_array($countries)) && (count($countries))) {
									echo "<select id=\"country_id\" name=\"country_id\" onchange=\"provStateFunction();\">\n";
									echo "<option value=\"0\"".((!$PROCESSED["country_id"]) ? " selected=\"selected\"" : "").">-- Select Country --</option>\n";
									foreach ($countries as $country) {
										echo "<option value=\"".(int) $country["countries_id"]."\"".(($PROCESSED["country_id"] == $country["countries_id"]) ? " selected=\"selected\"" : "").">".html_encode($country["country"])."</option>\n";
									}
									echo "</select>\n";
								} else {
									echo "<input type=\"hidden\" id=\"countries_id\" name=\"countries_id\" value=\"0\" />\n";
									echo "Country information not currently available.\n";
								}
								?>
							</div>
						</div>
                        <!--- End control-group ---->

						<div class="control-group">
							<label id="prov_state_label" for="prov_state_div" class="control-label">Province / State:</label>
							<div class="controls">
								<div id="prov_state_div">Please select a <strong>Country</strong> from above first.</div>
							</div>
						</div>
                        <!--- End control-group ---->

						<div class="control-group">
                            <label for="city" class="control-label form-nrequired">City:</label>
							<div class="controls">
								<input type="text" id="city" name="city" value="<?php echo ((isset($PROCESSED["city"])) ? html_encode($PROCESSED["city"]) : ""); ?>" maxlength="35" />
							</div>
						</div>
                        <!--- End control-group ---->

						<div class="control-group">
                            <label for="address" class="control-label form-nrequired">Address:</label>
							<div class="controls">
								<input type="text" id="address" name="address" value="<?php echo ((isset($PROCESSED["address"])) ? html_encode($PROCESSED["address"]) : ""); ?>" maxlength="255" />
							</div>
						</div>
                        <!--- End control-group ---->

						<div class="control-group">
                            <label for="postcode" class="control-label form-nrequired">Post / Zip Code:</label>
							<div class="controls">
								<input type="text" id="postcode" name="postcode" value="<?php echo ((isset($PROCESSED["postcode"])) ? html_encode($PROCESSED["postcode"]) : ""); ?>" maxlength="7" />
                                <span class="content-small">(<strong>Example:</strong> K7L 3N6)</span>
							</div>
						</div>
                        <!--- End control-group ---->

						<div class="control-group">
                            <label for="office_hours" class="control-label form-nrequired">Office Hours:</label>
							<div class="controls">
                                <textarea id="office_hours" class="expandable" name="office_hours"><?php echo (isset($PROCESSED["office_hours"]) && $PROCESSED["office_hours"] ? html_encode($PROCESSED["office_hours"]) : ""); ?></textarea>
							</div>
						</div>
                        <!--- End control-group ---->

						<div class="control-group">
                            <label for="notes" class="control-label form-nrequired">General Comments:</label>
							<div class="controls">
                                <textarea id="notes" class="expandable" name="notes"><?php echo ((isset($PROCESSED["notes"]) && $PROCESSED["notes"]) ? html_encode($PROCESSED["notes"]) : ""); ?></textarea>
							</div>
						</div>
                        <!--- End control-group ---->

						<h2>Permissions</h2>
						<?php
						if (strtolower($ENTRADA_USER->getActiveGroup()) == "medtech" && strtolower($ENTRADA_USER->getActiveRole()) == "admin") {
							$query		= "	SELECT DISTINCT o.`organisation_id`, o.`organisation_title`
											FROM `" . AUTH_DATABASE . "`.`organisations` o";
						} else {
							$query		= "	SELECT DISTINCT o.`organisation_id`, o.`organisation_title`
											FROM `".AUTH_DATABASE."`.`user_access` ua
											JOIN `" . AUTH_DATABASE . "`.`organisations` o
											ON ua.`organisation_id` = o.`organisation_id`
											WHERE ua.`user_id` = " . $db->qstr($ENTRADA_USER->getId()). "
											AND ua.`app_id` = " . $db->qstr(AUTH_APP_ID);
						}
						$all_orgs = $db->GetAll($query);
						if ($all_orgs) {
                            ?>
                            <div class="row-fluid">
                                <div class="span4">
                                    <label for="organisations"><strong>Organisation</strong></label><br />
                                    <select id="organisations" name="organisations">
                                        <?php
                                        foreach($all_orgs as $a_org) {
                                            echo build_option($a_org["organisation_id"], ucfirst($a_org["organisation_title"]), $selected);
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="span4">
                                    <label for="groups"><strong>Groups</strong></label><br />
                                    <select id="groups" name="groups">
                                        <option value="0">Select a Group</option>
                                    </select>
                                </div>
                                <div class="span4">
                                    <label for="roles"><strong>Role</strong></label><br />
                                    <select id="roles" name="roles">
                                        <option value="0">Select a Role</option>
                                    </select>
                                </div>
                            </div>
                            <br />
                            <div class="row-fluid">
                            	<input id="add_permissions" name="add_permissions" type="button" value="Add" class="btn pull-right" />
                            </div>
                            <hr />
                            <?php
                            $initial_permissions = array();

                            foreach ($all_orgs as $org) {
                                ?>
                                <div class="row-fluid" id="<?php echo "perm_organisation_" . $org["organisation_id"]; ?>_holder">
                                    <div class="span12">
                                        <table id="<?php echo "perm_organisation_" . $org["organisation_id"]; ?>" class="table table-bordered table-condensed">
                                            <caption>
                                                <h2 style="text-align: left;"><?php echo $org["organisation_title"]; ?></h2>
                                            </caption>
                                            <tbody class="permissions_profiles">
                                                <tr>
                                                    <td colspan="3"><h3>Profiles</h3></td>
                                                </tr>
                                                <?php
                                                $query = "	SELECT ua.*, o.`organisation_id`, o.`organisation_title`, ud.`clinical`, ud.`entry_year`, ud.`grad_year`
                                                            FROM `".AUTH_DATABASE."`.`user_access` ua
                                                            JOIN `" . AUTH_DATABASE . "`.`organisations` o
                                                            ON ua.`organisation_id` = o.`organisation_id`
                                                            JOIN `".AUTH_DATABASE."`.`user_data` ud
                                                            ON ua.`user_id` = ud.`id`
                                                            AND ua.`organisation_id` = " . $db->qstr($org["organisation_id"]) . "
                                                            WHERE ua.`user_id` = " . $db->qstr($PROXY_ID) ."
                                                            AND ua.`app_id` = " . $db->qstr(AUTH_APP_ID);

                                                $results = $db->GetAll($query);
                                                if ($results) {
                                                    foreach ($results as $result) {
                                                        switch (strtolower($result["group"])) {
                                                            case "faculty":
                                                                $checked = ($result["clinical"] ? "checked" : "");
                                                                $options = "<input id=\"clinical_" . $result["organisation_id"] . "\" name=\"clinical_"  . $result["organisation_id"] . "\" type=\"checkbox\" " . $checked . " /><label for=\"clincal" . $result["organisation_id"] . "\">This new user is a <strong>clinical</strong> faculty member.</label>";
                                                            break;
                                                            case "student":
                                                                $options = build_entry_grad_year_container($result["entry_year"], $result["grad_year"]);
                                                            break;
                                                            default:
                                                                $options = "";
                                                            break;
                                                        }
                                                        $query = "SELECT sg.`id`
                                                                  FROM " . AUTH_DATABASE . ".`system_groups` sg
                                                                  WHERE sg.`group_name` = " . $db->qstr(strtolower($result["group"]));
                                                        $group_id = $db->GetOne($query);

                                                        $query = "SELECT sr.`id`
                                                                  FROM " . AUTH_DATABASE . ".`system_roles` sr
                                                                  WHERE sr.`role_name` = " . $db->qstr(strtolower($result["role"]));
                                                        $role_id = $db->GetOne($query);

                                                        $initial_acl["access_id"] = $result["id"];
                                                        $initial_acl["org_id"] = $result["organisation_id"];
                                                        $initial_acl["group_id"] = $group_id;
                                                        $initial_acl["role_id"] = $role_id;
                                                        $initial_acl["clinical"] = $result["clinical"];
                                                        $initial_acl["entry_year"] = $result["entry_year"];
                                                        $initial_acl["grad_year"] = $result["grad_year"];
                                                        $initial_permissions[] = $initial_acl;

                                                        echo "<tr id=\"" . $result["organisation_id"] . "_" . $group_id . "_" . $role_id . "\">\n";
                                                        echo "	<td>" . ucfirst($result["group"]) . " / " . ucfirst($result["role"]) . "</td>\n";
                                                        echo "	<td>" . $options . "</td>\n";
                                                        echo "	<td><a data-group=\"".ucfirst($result["group"]) ."\" class=\"remove_perm pull-left\" href=\"#\"><img src=\"" . ENTRADA_URL . "/images/action-delete.gif\"></a></td>\n";
                                                        echo "</tr>";
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                            <tbody class="permissions_options">
                                                <tr>
                                                    <td colspan="3"><h3>Options</h3></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="departments-list">
                                                        <div class="control-group">
                                                            <label class="control-label" for="<?php echo "in_departments_" . $org["organisation_id"]; ?>">Departments</label>
                                                            <div class="controls">
                                                                <select id="<?php echo "in_departments_" . $org["organisation_id"]; ?>" name="<?php echo "in_departments_" . $org["organisation_id"]; ?>" style="">
                                                                    <option value="0">-- Select Departments --</option>
                                                                    <?php
                                                                    foreach($DEPARTMENT_LIST as $organisation_id => $dlist) {
                                                                        if ($org["organisation_id"] == $organisation_id){
                                                                            foreach($dlist as $d){
                                                                                if (!array_key_exists($d["department_id"], $PROCESSED_DEPARTMENTS)) {
                                                                                    echo build_option($d["department_id"], $d["department_title"], false);
                                                                                } else {
                                                                                    echo build_option($d["department_id"], $d["department_title"], true);
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                                <div class="help-inline"><strong>Note:</strong> Selected departments will appear here.</div>
                                                            </div>
                                                        </div>
                                                        <ol id="departments_container_<?php echo $org["organisation_id"]; ?>" class="sortableList" style="display: block;">
                                                            <?php
                                                            if (is_array($PROCESSED_DEPARTMENTS)) {
                                                                foreach($PROCESSED_DEPARTMENTS as $department_id => $department_title) {
                                                                    $query = "	SELECT d.`department_id`, d.`department_title`
                                                                                FROM `".AUTH_DATABASE."`.`departments` d
                                                                                WHERE d.`department_id` = " . $db->qstr($department_id) . "
                                                                                AND d.`organisation_id` = " . $db->qstr($org["organisation_id"]);
                                                                    $result = $db->GetRow($query);
                                                                    if ($result) {
                                                                        echo "<li id=\"dept_" . $department_id . "\"><i class=\"icon-home\" id=\"dept_" . $department_id . "\"></i>&nbsp;&nbsp;&nbsp;" . $department_title . " <a class=\"remove_dept\" href=\"\"><img src=\"" . ENTRADA_URL . "/images/action-delete.gif\" alt=\"Delete\"></a></li>";
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </ol>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <?php
                            }
                            //create final JSON object
                            $initial_permissions = array("acl" => $initial_permissions);
                            $initial_permissions = json_encode((object) $initial_permissions);

                            $initial_departments = array();
                            if (is_array($PROCESSED_DEPARTMENTS)) {
                                echo "<br />";
                                foreach($PROCESSED_DEPARTMENTS as $department_id => $department_title) {
                                    $department_accum = array();
                                    $department_accum["department_id"] = $department_id;
                                    $department_accum["department_title"] = $department_title;
                                    echo "<br />";
                                    $initial_departments[] = $department_accum;
                                }
                            }
                            $initial_departments = array("dept_list" => $initial_departments);
                            $initial_departments = json_encode((object) $initial_departments);
                            ?>
                            <input id="my_departments" name="my_departments" type="hidden" value="0" />
                            <?php
                        }
                        ?>
						<h2>Notification Options</h2>
						<div class="row-fluid">
							<input type="checkbox" id="send_notification" name="send_notification" value="1"<?php echo (((isset($_POST["send_notification"])) && ((int) $_POST["send_notification"])) ? " checked=\"checked\"" : ""); ?> onclick="toggle_visibility_checkbox(this, 'send_notification_msg')" />
							<label for="send_notification" class="form-nrequired">Send this new user a password reset e-mail after updating their profile.</label>

							<div id="send_notification_msg" style="display: none;">
								<label for="notification_message" class="form-required">Notification Message</label>
								<textarea id="notification_message" name="notification_message" rows="10" cols="65" style="width: 100%; height: 200px"><?php echo ((isset($_POST["notification_message"])) ? html_encode($_POST["notification_message"]) : $DEFAULT_NEW_USER_NOTIFICATION); ?></textarea>
								<span class="content-small"><strong>Available Variables:</strong> %firstname%, %lastname%, %username%, %password_reset_url%, %application_url%, %application_name%</span>
							</div>
						</div>
						<?php
						load_rte();
						if ($custom_fields) {
							$pub_types = array (
								"ar_poster_reports"				=> array("id_field" => "poster_reports_id", "title" => "title"),
								"ar_peer_reviewed_papers"		=> array("id_field" => "peer_reviewed_papers_id", "title" => "title"),
								"ar_non_peer_reviewed_papers"	=> array("id_field" => "non_peer_reviewed_papers_id", "title" => "title"),
								"ar_book_chapter_mono"			=> array("id_field" => "book_chapter_mono_id", "title" => "title"),
								"ar_conference_papers"			=> array("id_field" => "conference_papers_id", "title" => "lectures_papers_list")
							);
							echo "<h2>Department Specific Information</h2>";
							$NOTICESTR = array();
							add_notice("The information below has been requested by departments the user is a member of. This information is considered public and may be published on department websites.");
							echo display_notice();
							echo "<div class=\"tabbable departments\">";
							echo "<ul class=\"nav nav-tabs\">";
							$i = 0;
							foreach ($PROCESSED_DEPARTMENTS as $department_id => $department) {
								if (count($custom_fields[$department_id]) >= 1) {
									?>
									<li class="<?php echo $i == 0 ? "active" : ""; ?>"><a data-toggle="tab" href="#dep-<?php echo $department_id; ?>"><?php echo strlen($department) > 15 ? substr($department, 0, 15)."..." : $department; ?></a></li>
									<?php
									$i++;
								}
							}
							echo "</ul>";

							echo "<div class=\"tab-content\">";
							$i = 0;
							foreach ($PROCESSED_DEPARTMENTS as $department_id => $department) {
								if (count($custom_fields[$department_id]) >= 1) {
								echo "<div class=\"tab-pane ".($i == 0 ? "active" : "")."\" id=\"dep-".$department_id."\">";
								echo "<h3>".$department."</h3>";
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

								echo "<h3>Publications on ".$department." Website</h3>";

								foreach ($pub_types as $type_table => $data) {
									$query = "	SELECT a.`".$data["id_field"]."` AS `id`, a.`".$data["title"]."` AS `title`, a.`year_reported`, b.`id` AS `dep_pub_id`
												FROM `".$type_table."` AS a
												LEFT JOIN `profile_publications` AS b
												ON a.`proxy_id` = b.`proxy_id`
												AND b.`pub_id` = a.`".$data["id_field"]."`
												AND (b.`dep_id` = ".$db->qstr($department_id). " || b.`dep_id` IS NULL)
												WHERE a.`proxy_id` = ".$db->qstr($user_record["id"]);
									$pubs = $db->GetAll($query);
									if ($pubs) { ?>
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
											<?php foreach ($pubs as $publication) { ?>
												<tr data-id="<?php echo $publication["id"]; ?>">
													<td><?php echo $publication["title"]; ?></td>
													<td><?php echo $publication["year_reported"]; ?></td>
													<td><input type="checkbox" name="publications[<?php echo str_replace("ar_", "", $type_table); ?>][<?php echo $department_id; ?>][<?php echo $publication["id"]; ?>]" <?php echo ($publication["dep_pub_id"] != NULL ? "checked=\"checked\"" : ""); ?> /></td>
												</tr>
											<?php } ?>
											</tbody>
										</table>
										<?php
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

						<br />
						<div class="pull-left"><input type="button" class="btn" value="Cancel" onclick="window.location='<?php echo ENTRADA_RELATIVE; ?>/admin/users/manage?id=<?php echo $PROXY_ID; ?>'" /></div>
						<div class="pull-right"><input type="submit" class="btn btn-primary" value="Save" /></div>
						<input type="hidden" id="permissions" name="permissions" value="0" />
					</form>
			<div style="display: none;" id="entry_grad_year_container">
                <?php echo build_entry_grad_year_container($PROCESSED["entry_year"], $PROCESSED["grad_year"]); ?>
            </div>
			<style>
				.departments textarea.expandable.expanded {min-height:60px;}
				td.departments-list ol {margin-left:170px;}
				.time-wrapper {display:none;}
				td {
					text-align: left;
					white-space: normal;
				}
				table.tableList tbody tr td {
					text-align: left;
					white-space: normal;
				}

				th {
					color: #11335D; font-family: 'Century Gothic',Helvetica,Arial,sans-serif; font-size: 13px;
				}

				li a.remove_dept {
					display: block;
					float: right;
				}

				textarea.expandable {
					width: 75%;
				}
			</style>
			<script type="text/javascript">
					function provStateFunction(country_id, province_id) {
						var url_country_id = 0
						var url_province_id = 0;

						if (country_id != undefined) {
							url_country_id = country_id;
						} else if ($('country_id')) {
							url_country_id = $('country_id').getValue();
						}

						if (province_id != undefined) {
							url_province_id = province_id;
						} else if ($('province_id')) {
							url_province_id = $('province_id').getValue();
						}

						var url = '<?php echo webservice_url("province"); ?>?countries_id=' + url_country_id + '&prov_state=' + url_province_id;

						new Ajax.Updater($('prov_state_div'), url, {
							method:'get',
							onComplete: function (init_run) {

								if ($('prov_state').type == 'select-one') {
									$('prov_state_label').removeClassName('form-nrequired');
									$('prov_state_label').addClassName('form-required');
									if (!init_run) {
										$("prov_state").selectedIndex = 0;
									}
								} else {
									$('prov_state_label').removeClassName('form-required');
									$('prov_state_label').addClassName('form-nrequired');
									if (!init_run) {
										$("prov_state").clear();
									}
								}
							}
						});
					};

					jQuery(document).ready(function($) {
						<?php echo "var my_departments = " . $initial_departments . ";"; ?>
						$('input[name=my_departments]').val(JSON.stringify(my_departments));

						$('select[name^=in_departments_]').live("change", function() {
							var dept_id = $(this).val();
							var dept_text = $(this).find(":selected").text();
							var org_id = $(this).attr("id").split("_")[2];
							var remove_link = "<a class=\"remove_dept\" href=\"\"><img src=\"" + "<?php echo ENTRADA_URL; ?>" + "/images/action-delete.gif\"></a>";
							var content = "<li id=\"dept_" + dept_id + "\"><i class=\"icon-home\"></i>&nbsp;&nbsp;&nbsp;" + dept_text + remove_link + "</li>";
							$('#departments_container_' + org_id).append(content);
							$('#departments_notice_' + org_id).hide();
							$('#departments_container_' + org_id).show();
							temp_dept = {};
							temp_dept["department_id"] = dept_id;
							temp_dept["department_text"] = dept_text;
							if (my_departments.dept_list != null) {
								my_departments.dept_list.push(temp_dept);
							} else {
								my_departments.dept_list = [];
								my_departments.dept_list.push(temp_dept);
							}
							$('input[name=my_departments]').val(JSON.stringify(my_departments));
							//remove selected item and reset the select list
							$('#in_departments_' + org_id + ' option[value=' + dept_id + ']').remove();
							$('select[name^=in_departments_]').val(0);
						});

						$('a.remove_dept').live("click", function(event) {
							event.preventDefault();
							var dept_id = $(this).closest("li").attr("id").split("_")[1];
							var dept_text = $(this).closest("li").text();
							var org_id = $(this).closest("ol").attr("id").split("_")[2];
							$(this).closest("li").remove();
							if ($('#departments_container_' + org_id).children().size() == 0) {
								$('#departments_notice_' + org_id).show();
							}
							for(var i=0; i<my_departments.dept_list.length; i++) {
								if (my_departments.dept_list[i].department_id == dept_id) {
									my_departments.dept_list.splice(i, 1);
								}
							}
							$('input[name=my_departments]').val(JSON.stringify(my_departments));

							//add the dept back to the select list
							var option = $("<option></option>").text(dept_text);
							$(option).attr("value", dept_id);
							$('#in_departments_' + org_id).append(option);

							//now resort the select list by dept title
							var my_options = $('#in_departments_' + org_id + ' option');
							my_options.sort(function(a,b) {
								if (a.text > b.text) return 1;
								else if (a.text < b.text) return -1;
								else return 0
							});
							$('#in_departments_' + org_id).empty().append( my_options );
							$('select[name^=in_departments_]').val(0);
						});

						$('select[name=organisations]').live("change", function() {
							$('select[name=groups]').children().remove();
							$("#groups").append('<option value=\"0\">Select a Group</option>')
							var url = "<?php echo ENTRADA_URL . "/api/organisation-groups.api.php"; ?>";
							$.get(	url, { organisation_id: $(this).val() },
									function(data){
										for (var key in data) {
											if (data.hasOwnProperty(key)) {
											$("#groups").append('<option value=\"' + key + '\">' + data[key]+ '</option>');
											}
										}
										filterGroups();
									}, "json");
						});
						$('select[name=groups]').live("change", function() {
							$('select[name=roles]').children().remove();
							$("#roles").append('<option value=\"0\">Select a Role</option>')
							var url = "<?php echo ENTRADA_URL . "/api/organisation-roles.api.php"; ?>";
							$.get(	url, { organisation_id: $('#organisations option:selected').val(), group_id: $(this).val() },
									function(data){
										for (var key in data) {
											if (data.hasOwnProperty(key)) {
											$("#roles").append('<option value=\"' + key + '\">' + data[key]+ '</option>');
											}
										}
									}, "json");
						});

						$('#organisations option:first').attr('selected', true).change();

						<?php echo "var permissions = " . $initial_permissions . ";"; ?>
						$('input[name=permissions]').val(JSON.stringify(permissions));

						$("input[name=add_permissions]").live("click", function() {
							var group_id = $('#groups').val();
							var role_id = $('#roles').val();
							var org_id = $('#organisations').val();

							if (org_id == null || org_id == 0) {
								alert("Please select an organisation");
							} else if (group_id == null || group_id == 0) {
								alert("Please select a group.");
							} else if (role_id == null || role_id == 0) {
								alert("Please select a role.")
							} else {
								var group_text = $('#groups option[value=' + group_id + ']').text();
								$('#groups option[value=' + group_id + ']').remove();
								var role_text = $('#roles option[value=' + role_id + ']').text();
								$('select[name=roles]').children().remove();
								var options = "";
								var clinical = 0;
								var entry_year = 0;
								var grad_year = 0;
								if (group_text == "Faculty") {
									options = "<label for=\"clincal_" + org_id + "\">This new user is a <strong>clinical</strong> faculty member.</label><input id=\"clinical_"  + org_id + "\" name=\"clinical_"  + org_id + "\" type=\"checkbox\" checked />";
									clinical = 1;
								} else if (group_text == "Student") {
									options = $('#entry_grad_year_container').html();
									entry_year = $('#entry_year').val();
									grad_year = $('#grad_year').val();
								}
								$('#perm_organisation_' + $('#organisations').val() + ' > tbody.permissions_profiles:last').append('<tr id=\"' + org_id + '_' + group_id + '_' + role_id + '\"><td valign="top">' + group_text + ' / ' + role_text + '</td><td>' + options + '</td><td><a class=\"remove_perm\" data-group=\"' + group_text + '\" href=\"\"><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif"></a></td></tr>');
								$('#perm_organisation_' + $('#organisations').val() + '_holder').show();

								var temp_permissions = {"org_id" : org_id, "group_id" : group_id, "role_id" : role_id, "clinical" : clinical, "entry_year" : entry_year, "grad_year" : grad_year};
								permissions.acl.push(temp_permissions);
								$('input[name=permissions]').val(JSON.stringify(permissions));
							}
						});

						$('input[id^=clinical_]').live("change", function() {
							var org_id = $(this).attr("id").split("_")[1];
							for (i = 0; i < permissions.acl.length; i++) {
								//check the permissions array for the faculty role for this org
								if (permissions.acl[i].org_id == org_id
									&& permissions.acl[i].group_id == 3) {
									if ($(this).is(':checked')) {
										permissions.acl[i].clinical = 1;
									} else {
										permissions.acl[i].clinical = 0;
									}
								}
							}
							$('input[name=permissions]').val(JSON.stringify(permissions));
						});

						$('select[id=entry_year]').live("change", function() {
							var org_id = $(this).closest('table').attr("id").split("_")[2]
							for (i = 0; i < permissions.acl.length; i++) {
								if (permissions.acl[i].org_id == org_id) {
									permissions.acl[i].entry_year = $(this).val();
								}
							}
							$('input[name=permissions]').val(JSON.stringify(permissions));
						});

						$('select[id=grad_year]').live("change", function() {
							var org_id = $(this).closest('table').attr("id").split("_")[2];
							for (i = 0; i < permissions.acl.length; i++) {
								if (permissions.acl[i].org_id == org_id) {
                            		permissions.acl[i].grad_year = $(this).val();
								}
							}
							$('input[name=permissions]').val(JSON.stringify(permissions));
						});

						$("a.remove_perm").live("click", function(e) {
							e.preventDefault();
							var row_id = $(this).closest("tr").attr("id");
							var org_id = row_id.split("_")[0];
							var group_id = row_id.split("_")[1];
							var role_id = row_id.split("_")[2];
							for(var i=0; i<permissions.acl.length; i++) {
								if (permissions.acl[i].org_id == org_id &&
									permissions.acl[i].group_id == group_id &&
									permissions.acl[i].role_id == role_id) {
									permissions.acl.splice(i, 1);
								}
							}
							$('input[name=permissions]').val(JSON.stringify(permissions));

							if (org_id == $('#organisations').val()) {
								//add the group back to the select list
								var group_role = $(this).closest("tr").children()[1];
								group_role = $(group_role).text();
								var group_text = jQuery(this).data('group');
								var option = $("<option></option>").text(group_text);
								$(option).attr("value", group_id);
								$('#groups').append(option);

								//now resort the select list by group title
								var my_options = $('#groups option');
								my_options.sort(function(a,b) {
									if (a.text > b.text) return 1;
									else if (a.text < b.text) return -1;
									else return 0
								});
							}

							var myTable = $(this).closest("table");
							$(this).closest("tr").remove();
							if ($(myTable)[0].rows.length <= 4) {
								$("#department_callback" + org_id).children().remove();
								$(myTable).hide();
								my_departments.dept_list.splice(0,my_departments.dept_list.length);
								$('input[name=my_departments]').val(0);
								$('#departments_container_' + org_id).children().remove();
							}
						});

						$('table[id^=perm_organisation_]').each(function(index) {
							if ($(this).find('tbody tr').length <= 2) {
								$('#' + this.id + '_holder').hide();
							}
						});
						function filterGroups() {
							if ($('#groups option').length > 0) {
								var current_org = $('#organisations').val();
								for (i = 0; i < permissions.acl.length; i++) {
									if (permissions.acl[i].org_id == current_org) {
										$('#groups option[value=' + permissions.acl[i].group_id + ']').remove();
									}
								}
								//now resort the option list
								var my_options = $('#groups option');
								my_options.sort(function(a,b) {
									if (a.text > b.text) return 1;
									else if (a.text < b.text) return -1;
									else return 0
								});
							}
						}
					});
					</script>
					<?php
				break;
			} //end display switch
		} else {
			$ERROR++;
			$ERRORSTR[] = "In order to edit a user profile you must provide a valid identifier. The provided ID does not exist in this system.";

			echo display_error();

			application_log("notice", "Failed to provide a valid user identifer when attempting to edit a user profile.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to edit a user profile you must provide a user identifier.";

		echo display_error();

		application_log("notice", "Failed to provide user identifer when attempting to edit a user profile.");
	}
}